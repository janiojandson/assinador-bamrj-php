<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final: Rotas Táticas, Arquivo Legado, Radar de Inbox, SSO e Substituto Persistente
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
    case '/index':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password']) {
            header("Location: /setup_password"); exit();
        }
        require __DIR__ . '/../app/views/dashboard.php';
        break;

    case '/login': 
        require __DIR__ . '/../app/views/login.php'; 
        break;
    
    case '/setup_password':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/setup_password.php';
        break;

    case '/trocar_senha':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        $authCtrl = new \App\Controllers\AuthController();
        $authCtrl->trocarSenha();
        break;

    case '/logout':
        session_destroy();
        header("Location: /login");
        exit();
        break;

    case '/toggle_substitute':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        // 🔄 Persiste no BD — fonte de verdade
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT substituto_ativo FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $estado_atual = (bool)($stmt->fetchColumn() ?? false);
        $novo_estado = !$estado_atual;
        $db->prepare("UPDATE users SET substituto_ativo = ? WHERE id = ?")
           ->execute([$novo_estado ? TRUE : FALSE, $_SESSION['user_id']]);
        $_SESSION['is_substitute'] = $novo_estado;
        header("Location: /index");
        exit();
        break;    

    case '/sso_sigef':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        $authCtrl = new \App\Controllers\AuthController();
        $authCtrl->redirectToSigef();
        break;    

    case '/sso_return':
        $authCtrl = new \App\Controllers\AuthController();
        $authCtrl->loginFromSigef();
        break;

    case '/upload':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Operador') { header("Location: /index"); exit(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upCtrl = new \App\Controllers\UploadController();
            $upCtrl->handleUpload(); 
        } else {
            require __DIR__ . '/../app/views/upload_process.php'; 
        }
        break;
        
    case '/upload_legado':
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Operador', 'Admin'])) { header("Location: /index"); exit(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upCtrl = new \App\Controllers\UploadController();
            $upCtrl->handleLegacyUpload(); 
        } else {
            require __DIR__ . '/../app/views/upload_legacy.php';
        }
        break;

    case '/view':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/viewer.php';
        break;

    case '/edit':
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Operador', 'Admin'])) { header("Location: /index"); exit(); }
        $docCtrl = new \App\Controllers\DocumentController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $docCtrl->handleEdit();
        } else {
            require __DIR__ . '/../app/views/edit.php';
        }
        break;

    case '/edit_process':
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Operador', 'Admin'])) { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/edit_process.php';
        break;

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/acesso_publico':
        \App\Controllers\ArchiveController::simulatePublicAccess();
        break;

    case '/admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/admin_users.php';
        break;

    case '/api/check_inbox':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['count' => 0]); exit(); }
        
        $db = \App\Core\Database::getConnection();
        $role = $_SESSION['role'] ?? '';
        
        // 🔄 Sincroniza substituto com BD
        $stmt_sub = $db->prepare("SELECT substituto_ativo FROM users WHERE id = ?");
        $stmt_sub->execute([$_SESSION['user_id']]);
        $is_substitute = (bool)($stmt_sub->fetchColumn() ?? false);
        $_SESSION['is_substitute'] = $is_substitute;
        
        $count = 0;
        if ($role === 'Operador') {
            $stmt = $db->query("SELECT COUNT(*) FROM documents WHERE status NOT IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado')");
            $count = (int)$stmt->fetchColumn();
        } elseif (in_array($role, ['Gestor_Financeiro', 'Gestor_Financeiro_Substituto', 'Chefe_Departamento', 'Agente_Fiscal', 'Ordenador_Despesas'])) {
            $status_map = [
                'Gestor_Financeiro' => ['Caixa de Entrada - Gestor Financeiro'],
                'Gestor_Financeiro_Substituto' => ['Caixa de Entrada - Gestor Financeiro'],
                'Chefe_Departamento' => ['Aguardando Assinatura - Chefe Departamento'],
                'Agente_Fiscal' => ['Aguardando Assinatura - Agente Fiscal'],
                'Ordenador_Despesas' => ['Aguardando Assinatura - Ordenador'],
            ];
            $statuses = $status_map[$role] ?? [];
            if (!empty($statuses)) {
                $in = str_repeat('?,', count($statuses) - 1) . '?';
                $stmt = $db->prepare("SELECT COUNT(*) FROM documents WHERE status IN ($in)");
                $stmt->execute($statuses);
                $count = (int)$stmt->fetchColumn();
            }
        }
        echo json_encode(['count' => $count]);
        exit();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1><a href='/'>Voltar ao início</a>";
        break;
}
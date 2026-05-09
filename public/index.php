<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final: Rotas Táticas, Arquivo Legado, Radar de Inbox, SSO e Substituto Persistente
 * 🐛 FIX: Adicionada rota /view como alias para /viewer (Bug de 404 na navegação)
 * 🗑️ FASE 4: Adicionada rota POST /cancelar_processo (Cancelamento pelo Operador)
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

    // 🐛 FIX: Rota /acesso_publico — Consulta Pública sem autenticação
    case '/acesso_publico':
        $archiveCtrl = new \App\Controllers\ArchiveController();
        $archiveCtrl->simulatePublicAccess();
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
        // 🐛 FIX: Usa 1/0 em vez de TRUE/FALSE para compatibilidade com PostgreSQL via PDO
        $db->prepare("UPDATE users SET substituto_ativo = ? WHERE id = ?")
           ->execute([$novo_estado ? 1 : 0, $_SESSION['user_id']]);
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
    case '/viewer':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/viewer.php';
        break;

    case '/edit':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/edit_process.php';
        break;

    // 🗑️ FASE 4: Rota POST para Cancelar Processo (Operador)
    case '/cancelar_processo':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Operador') { header("Location: /index"); exit(); }
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->cancelarProcesso();
        break;

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/admin/users':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->users();
        break;

    case '/migrate':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/migrate.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1><p>A rota solicitada não existe.</p><a href='/index'>Voltar ao início</a>";
        break;
}
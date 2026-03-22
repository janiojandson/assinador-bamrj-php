<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final: Rotas Táticas, Arquivo Legado e Radar de Inbox
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

    case '/login': require __DIR__ . '/../app/views/login.php'; break;
    
    case '/logout':
        session_destroy();
        header("Location: /login");
        exit();
        break;

    case '/setup_password':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/setup_password.php';
        break;

    /* =========================================
       ROTAS DE CRIAÇÃO E CONSULTA
       ========================================= */
    case '/upload':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Operador') { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/upload_process.php';
        break;
        
    case '/upload_legado':
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Operador', 'Admin'])) { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/upload_legacy.php';
        break;

    case '/view':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/viewer.php';
        break;

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/acesso_publico':
        \App\Controllers\ArchiveController::simulatePublicAccess();
        break;

    /* =========================================
       ROTAS DE AÇÃO MILITAR (Controllers Diretos)
       ========================================= */
    case '/process_action':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->processAction(); // Aprova ou Devolve
        break;

    case '/cancel':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->cancelProcess(); // Cancela o processo
        break;

    case '/upload_ne':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->uploadNE(); // Anexa a Nota de Empenho Final
        break;

    case '/edit':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->editProcess(); // O Controlador valida, atualiza e carrega a View sozinho
        break;

    /* =========================================
       RADAR TÁTICO (AJAX Polling do Dashboard)
       ========================================= */
    case '/api/check_inbox':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['count' => 0]); exit; }
        
        // Reutilizamos a lógica do dashboard para contar processos pendentes
        $docCtrl = new \App\Controllers\DocumentController();
        $docs = $docCtrl->getDashboardData(); 
        echo json_encode(['count' => count($docs)]);
        break;

    /* =========================================
       ADMINISTRAÇÃO
       ========================================= */
    case '/admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        require __DIR__ . '/../app/views/admin_users.php';
        break;

    case '/admin/delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->deleteUser($_GET['id'] ?? 0);
        break;

    case '/admin/reset_docs':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->resetDocuments();
        break;

    case '/admin/factory_reset':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        $adminCtrl = new \App\Controllers\AdminController();
        // CORRIGIDO AQUI: A sua função chama-se resetDatabase(), não factoryReset()
        $adminCtrl->resetDatabase(); 
        break;
    
    default:
        http_response_code(404);
        echo "<h1>404</h1><p>Erro: Rota não encontrada no perímetro do Assinador-BAMRJ.</p>";
        break;
}
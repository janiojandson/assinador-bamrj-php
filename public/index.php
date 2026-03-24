<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final: Rotas Táticas, Arquivo Legado, Radar de Inbox e SSO
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

    case '/logout':
        session_destroy();
        header("Location: /login");
        exit();
        break;

    case '/toggle_substitute':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        $_SESSION['is_substitute'] = !($_SESSION['is_substitute'] ?? false);
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

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/acesso_publico':
        \App\Controllers\ArchiveController::simulatePublicAccess();
        break;

    case '/process_action':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->processAction(); 
        break;

    case '/cancel':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->cancelProcess(); 
        break;

    case '/upload_ne':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->uploadNE(); 
        break;

    case '/edit':
        $docCtrl = new \App\Controllers\DocumentController();
        $docCtrl->editProcess(); 
        break;

    case '/api/check_inbox':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['count' => 0]); exit; }
        $dashCtrl = new \App\Controllers\DashboardController();
        $data = $dashCtrl->getDashboardData(); 
        echo json_encode(['count' => $data['inbox_count'] ?? 0]);
        break;

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
        $adminCtrl->resetDocs();
        break;

    case '/admin/factory_reset':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header("Location: /index"); exit(); }
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->factoryReset();
        break;
    
    default:
        http_response_code(404);
        echo "<div style='padding:40px; font-family:sans-serif; text-align:center;'>
                <h1 style='color:#d32f2f;'>⚠️ 404 - ALERTA DE ROTA</h1>
                <p>A página que tentou aceder não existe no perímetro do Assinador-BAMRJ.</p>
                <a href='/index' style='padding:10px 20px; background:#00447c; color:white; text-decoration:none; border-radius:4px;'>Voltar à Base</a>
              </div>";
        break;
}
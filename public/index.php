<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final Consolidada: Fase 10 (Gestão de Utilizadores Admin)
 */

// 1. Configurações de Erro e Sessão
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// 2. Autoload manual para carregar as Classes do diretório /app
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

// 3. Captura da URL solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4. MOTOR DE ROTEAMENTO (Substitui o app/routes.py do Python)
switch ($uri) {
    case '/':
    case '/index':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        // Bloqueio de Segurança: Trava de senha obrigatória
        if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password']) {
            header("Location: /setup_password");
            exit();
        }
        require __DIR__ . '/../app/views/dashboard.php';
        break;

    case '/login':
        require __DIR__ . '/../app/views/login.php';
        break;

    case '/setup_password':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require __DIR__ . '/../app/views/setup_password.php';
        break;

    case '/upload':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Operador') {
            header("Location: /index");
            exit();
        }
        require __DIR__ . '/../app/views/upload_process.php';
        break;

    case '/view':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require __DIR__ . '/../app/views/viewer.php';
        break;

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/acesso_publico':
        \App\Controllers\ArchiveController::simulatePublicAccess();
        break;

    case '/admin':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
            header("Location: /index");
            exit();
        }
        require __DIR__ . '/../app/views/admin_users.php';
        break;

    case '/admin/delete':
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
            header("Location: /index");
            exit();
        }
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->deleteUser($_GET['id'] ?? 0);
        break;

    case '/logout':
        session_destroy();
        header("Location: /login");
        exit();
        break;

    default:
        http_response_code(404);
        echo "<h1>404</h1><p>Erro: Rota não encontrada no perímetro do Assinador-BAMRJ.</p>";
        break;
} // <--- ESTE FECHAMENTO É ESSENCIAL
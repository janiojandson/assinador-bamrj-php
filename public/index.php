<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final Consolidada: Fase 10 (Gestão de Utilizadores Admin)
 * Arquiteto: Correção de Autoload Resiliente (Case Sensitivity Linux/Railway)
 */

// 1. Configurações de Erro e Sessão
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// 2. Autoload inteligente e resiliente a Case Sensitivity
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) return;
    
    $relative_class = substr($class, $len);
    $path = str_replace('\\', '/', $relative_class);
    
    // Tentativa 1: Caminho exato (Ex: app/Controllers/AuthController.php)
    $file_strict = $base_dir . $path . '.php';
    
    // Tentativa 2: Fallback Tático para Linux (Ex: app/controllers/AuthController.php)
    $path_parts = explode('/', $path);
    if (count($path_parts) > 1) {
        $path_parts[0] = strtolower($path_parts[0]); // Força a pasta raiz (Controllers, Models) para minúsculo
    }
    $file_fallback = $base_dir . implode('/', $path_parts) . '.php';

    // Verificação de existência
    if (file_exists($file_strict)) {
        require file_strict;
    } elseif (file_exists($file_fallback)) {
        require $file_fallback;
    }
});

// 3. Captura da URL solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4. MOTOR DE ROTEAMENTO
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
}
?>
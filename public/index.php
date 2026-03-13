<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Final Consolidada: Fase 11 (Rotas de Ação e Painel Admin)
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
    
    // Tentativa 1: Caminho exato
    $file_strict = $base_dir . $path . '.php';
    
    // Tentativa 2: Fallback Tático para Linux
    $path_parts = explode('/', $path);
    if (count($path_parts) > 1) {
        $path_parts[0] = strtolower($path_parts[0]); 
    }
    $file_fallback = $base_dir . implode('/', $path_parts) . '.php';

    if (file_exists($file_strict)) {
        require $file_strict; 
    } elseif (file_exists($file_fallback)) {
        require $file_fallback;
    }
});

// 3. Captura da URL solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 4. MOTOR DE ROTEAMENTO TÁTICO
switch ($uri) {
    // ---- ROTAS DE VISUALIZAÇÃO (VIEWS) ----
    case '/':
    case '/index':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
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

    case '/arquivo':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require __DIR__ . '/../app/views/arquivo.php';
        break;

    case '/view':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require __DIR__ . '/../app/views/viewer.php';
        break;

    // ---- ROTAS DE ACESSO E SESSÃO ----
    case '/logout':
        session_destroy();
        header("Location: /login");
        exit();
        break;

    case '/acesso_publico':
        \App\Controllers\ArchiveController::simulatePublicAccess();
        break;

    case '/toggle_substitute':
        if (isset($_SESSION['user_id'])) {
            $_SESSION['is_substitute'] = !($_SESSION['is_substitute'] ?? false);
        }
        header("Location: /index");
        exit();
        break;

    // ---- ROTAS DE ADMINISTRAÇÃO ----
    case '/admin/create_user':
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->createUser();
        break;

    case '/admin/edit_user':
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->editUser();
        break;

    case '/admin/delete':
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->deleteUser($_GET['id'] ?? 0);
        break;

    // ---- ROTAS DE DOCUMENTOS (Preparação para a próxima fase) ----
    case '/upload':
    case '/cancel':
    case '/upload_ne':
        // Blindagem temporária. Se bater aqui, sabemos que o botão funcionou!
        die("<h1>Em Construção</h1><p>A rota de manipulação de documentos está sendo blindada pelo Arquiteto. Aguarde o próximo pacote de deploy.</p>");
        break;

    // 💣 DETONADOR TÁTICO: Recriar Banco de Dados
    case '/reset_secreto_banco_1234':
        $adminCtrl = new \App\Controllers\AdminController();
        $adminCtrl->resetDatabase();
        break;

    default:
        http_response_code(404);
        echo "<h1>404</h1><p>Erro: Rota não encontrada no perímetro do Assinador-BAMRJ.</p>";
        break;
}
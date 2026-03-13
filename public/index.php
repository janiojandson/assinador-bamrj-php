<?php
/**
 * FRONT CONTROLLER - ASSINADOR BAMRJ
 * Versão Corrigida: Fase 5 (Dashboard & Tramitação)
 */

// Ativar exibição de erros para debug no Railway
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Autoload manual: Mapeia o namespace 'App\' para a pasta '../app/'
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

// Limpeza da URI para roteamento
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// BLOCO DE ROTEAMENTO (Tradução fiel do app/routes.py)
switch ($uri) {
    case '/':
    case '/index':
        // Proteção de Rota: Se não estiver logado, abortar para login
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        // Carrega a View do Dashboard (Fase 5)
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

    case '/view':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        // Rota preparada para o visualizador de PDFs (Fase 6)
        require __DIR__ . '/../app/views/viewer.php';
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
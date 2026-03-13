<?php
// Ativar exibição de erros para debug no Railway (Remover em produção real)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Autoload manual para as nossas classes (Simulando o que o Flask faz internamente)
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

// Roteamento Simples (Tradução das rotas do routes.py)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
    case '/index':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        echo "<h1>Bem-vindo ao Dashboard BAMRJ</h1><p>Usuário: " . $_SESSION['name'] . "</p><a href='/logout'>Sair</a>";
        break;

    case '/login':
        // Por enquanto, apenas um teste. Criaremos a View de Login a seguir.
        require __DIR__ . '/../app/views/login.php';
        break;

    case '/logout':
        session_destroy();
        header("Location: /login");
        break;

    default:
        http_response_code(404);
        echo "404 - Rota não encontrada na Base de Dados.";
        break;
}
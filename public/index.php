<?php
// public/index.php

// Inicia a sessão de forma segura
session_start();

// Autoloader simples
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Captura a rota diretamente e de forma limpa
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Força o dashboard se acederem à raiz
if ($route == '/' || $route == '') {
    $route = '/dashboard';
}

// Roteamento Blindado
switch ($route) {
    case '/login':
        $controller = new \App\Controllers\AuthController();
        $controller->login();
        break;
        
    case '/dashboard':
        $controller = new \App\Controllers\MainController();
        $controller->dashboard();
        break;
        
    case '/logout':
        session_destroy();
        header("Location: /login");
        exit;
        
    default:
        http_response_code(404);
        echo "<div style='font-family: Arial; padding: 50px; text-align: center;'>";
        echo "<h1>🚨 404 - Rota não encontrada</h1>";
        echo "<p>O sistema tentou aceder a: <strong>" . htmlspecialchars($route) . "</strong></p>";
        echo "<a href='/login'>Voltar para o Início</a>";
        echo "</div>";
        break;
}
?>
<?php
// public/index.php

// 1. LIGAR O RADAR DE ERROS (Apenas para a fase de homologação)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão de forma segura
session_start();

// 2. AUTOLOADER INTELIGENTE (Resolve o problema do Linux)
spl_autoload_register(function ($class_name) {
    $path = str_replace('\\', '/', $class_name);
    
    // Força o prefixo 'App/' a virar 'app/' (minúsculo) para bater com a pasta
    if (strpos($path, 'App/') === 0) {
        $path = 'app/' . substr($path, 4);
    }
    
    $file = __DIR__ . '/../' . $path . '.php';
    
    if (file_exists($file)) {
        require $file;
    } else {
        // Se não achar o ficheiro, avisa na tela para não dar Erro 500 cego
        die("Erro Tático: O ficheiro da classe não foi encontrado no caminho: " . $file);
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
    
    case '/setup-password':
        $controller = new \App\Controllers\SetupPasswordController();
        $controller->index();
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
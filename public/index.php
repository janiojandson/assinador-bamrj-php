<?php
// Inicia a sessão de forma segura
session_start();

// Autoloader simples (já que não temos Composer)
spl_autoload_register(function ($class_name) {
    // Converte App\Controllers\MainController para app/Controllers/MainController.php
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Captura a rota (ex: /login, /dashboard)
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$route = str_replace($script_name, '', $request_uri);

if ($route == '/' || $route == '') {
    $route = '/dashboard';
}

// Roteamento Básico
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
        echo "404 - Página não encontrada.";
        break;
}
?>
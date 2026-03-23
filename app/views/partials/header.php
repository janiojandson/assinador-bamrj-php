<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?= $page_title ?? 'Assinador BAMRJ' ?></title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        body { background-color: #f4f7f6; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        .navbar { background-color: #002244; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-bottom: 4px solid #ffcc00; }
        .navbar-logo img { height: 45px; background: white; padding: 3px; border-radius: 4px; }
        .navbar-links { display: flex; align-items: center; gap: 15px; }
        .navbar-links a { color: white; text-decoration: none; font-weight: bold; padding: 8px 12px; border-radius: 4px; transition: 0.3s; display: inline-block; border: 1px solid transparent; }
        
        /* Botões dinâmicos */
        .navbar-links .btn-inicio { border: 1px solid white; background-color: rgba(255,255,255,0.1); }
        .navbar-links .btn-inicio:hover { background-color: rgba(255,255,255,0.2); }
        .navbar-links .logout { background-color: #dc3545; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .navbar-links .logout:hover { background-color: #c82333; }
        
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
    </style>
</head>
<body>
    <?php if (!isset($hide_navbar) || !$hide_navbar): ?>
    <nav class="navbar">
        <div class="navbar-logo" style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ">
            <h2 style="margin: 0; letter-spacing: 1px; font-size: 1.2em; text-transform: uppercase;">
                <?= $page_title ?? 'ASSINADOR ELETRÔNICO' ?>
            </h2>
        </div>
        <div class="navbar-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') !== 'Usuário Comum'): ?>
                    <span style="color: #a1c6ea;">👤 <?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
                <?php endif; ?>
                
                <?php 
                // 🧠 MOTOR DINÂMICO DE NAVEGAÇÃO
                $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if ($current_uri === '/' || $current_uri === '/index'): ?>
                    <a href="/logout" class="logout">Sair do Sistema</a>
                <?php else: ?>
                    <a href="/index" class="btn-inicio">🏠 INÍCIO / DASHBOARD</a>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    <div class="container">
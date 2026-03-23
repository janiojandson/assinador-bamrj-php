<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?= $page_title ?? 'Assinador BAMRJ' ?></title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        body { background-color: #f4f7f6; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        
        /* Barra Superior Padrão SIGEF */
        .navbar { background-color: #002244; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-bottom: 4px solid #ffcc00; }
        .navbar-logo img { height: 45px; background: white; padding: 3px; border-radius: 4px; }
        .navbar-links { display: flex; align-items: center; gap: 15px; }
        .navbar-links a { color: white; text-decoration: none; font-weight: bold; padding: 8px 12px; border-radius: 4px; transition: 0.3s; display: inline-block; }
        
        /* Botão Sair Vermelho (Sempre visível) */
        .navbar-links .logout { background-color: #dc3545; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .navbar-links .logout:hover { background-color: #c82333; }
        
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        
        /* 🟢 Estilo do Botão Início (Idêntico à referência SIGEF) */
        .btn-nav-inicio {
            background-color: #e9ecef;
            color: #002244;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            border: 1px solid #ced4da;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        .btn-nav-inicio:hover { background-color: #dde2e6; border-color: #b1bbc4; }

        /* 🟢 Estilo do Botão SSO SIGEF */
        .btn-nav-sigef {
            background-color: #17a2b8;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            border: 1px solid #117a8b;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .btn-nav-sigef:hover { background-color: #138496; border-color: #0c5460; }
    </style>
</head>
<body>
    <?php if (!isset($hide_navbar) || !$hide_navbar): ?>
    <nav class="navbar">
        <div class="navbar-logo" style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ">
            <h2 style="margin: 0; letter-spacing: 1px; font-size: 1.2em; text-transform: uppercase;">
                ASSINADOR BAMRJ
            </h2>
        </div>
        <div class="navbar-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') !== 'Usuário Comum'): ?>
                    <span style="color: #a1c6ea;">👤 <?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
                <?php endif; ?>
                
                <a href="/logout" class="logout">Sair do Sistema</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container">
        <?php if (!isset($hide_navbar) || !$hide_navbar): ?>
        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="/index" class="btn-nav-inicio">🏠 Dashboard / Início</a>
            
            <?php if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') !== 'Usuário Comum'): ?>
                <a href="/sso_sigef" target="_blank" class="btn-nav-sigef">
                    🔄 Acessar SIGEF
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
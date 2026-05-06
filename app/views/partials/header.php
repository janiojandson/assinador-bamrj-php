<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title><?= $page_title ?? 'Trâmite BAMRJ' ?></title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        body { background-color: #f4f7f6; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        
        /* Barra Superior Padrão SIGEF/Assinador */
        .navbar { background-color: #002244; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-bottom: 4px solid #ffcc00; }
        .navbar-logo img { height: 45px; background: white; padding: 3px; border-radius: 4px; }
        .navbar-links { display: flex; align-items: center; gap: 15px; }
        .navbar-links a { color: white; text-decoration: none; font-weight: bold; padding: 8px 12px; border-radius: 4px; transition: 0.3s; display: inline-block; }
        
        /* Botão Sair Vermelho */
        .navbar-links .logout { background-color: #dc3545; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .navbar-links .logout:hover { background-color: #c82333; }
        
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        
        /* 🟢 Estilo do Botão Início */
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

        /* =========================================================
           PADRONIZAÇÃO DE BOTÕES E BADGES (Estilo Unificado)
        ========================================================= */
        .btn { display: inline-block; padding: 8px 16px; font-size: 0.95em; font-weight: bold; text-align: center; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; transition: all 0.2s ease-in-out; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.15); }
        
        .btn-primary { background: #004488; color: white; }
        .btn-primary:hover { background: #003366; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffcc00; color: #002244; }
        .btn-warning:hover { background: #e6b800; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-info:hover { background: #138496; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }

        /* Alerta de Novo Documento */
        @keyframes pulso-suave {
            0%   { background-color: #ffcc00; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            50%  { background-color: #ffe066; box-shadow: 0 4px 15px rgba(255, 204, 0, 0.6); }
            100% { background-color: #ffcc00; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        }
        .alerta-piscando {
            display: block !important;
            animation: pulso-suave 2.5s infinite ease-in-out !important;
        }
    </style>
</head>
<body>
    <?php 
    // 🔄 Carrega o estado do substituto do BD (persistência entre sessões)
    $substituto_ativo_bd = false;
    if (isset($_SESSION['user_id'])) {
        try {
            $db_sub = \App\Core\Database::getConnection();
            $stmt_sub = $db_sub->prepare("SELECT substituto_ativo FROM users WHERE id = ?");
            $stmt_sub->execute([$_SESSION['user_id']]);
            $substituto_ativo_bd = (bool)($stmt_sub->fetchColumn() ?? false);
            // Sincroniza a sessão com o BD
            $_SESSION['is_substitute'] = $substituto_ativo_bd;
        } catch (\Exception $e) { /* silencioso */ }
    }
    ?>

    <?php if ($substituto_ativo_bd): ?>
    <div class="banner-substituto">
        ⚠️ MODO SUBSTITUTO ATIVO — Você está atuando com permissões hierárquicas estendidas.
        <a href="/toggle_substitute">❌ Desativar Substituto</a>
    </div>
    <?php endif; ?>

    <?php if (!isset($hide_navbar) || !$hide_navbar): ?>
    <nav class="navbar">
        <div class="navbar-logo" style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ">
            <h2 style="margin: 0; letter-spacing: 1px; font-size: 1.2em; text-transform: uppercase;">
                TRAMITAÇÃO DE NE BAMRJ
            </h2>
        </div>
        <div class="navbar-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') !== 'Usuário Comum'): ?>
                    <span style="color: #ffcc00; font-size: 0.9em;">👤 <?= htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']) ?></span>
                <?php endif; ?>
                <a href="/trocar_senha" class="btn-nav-inicio" style="background-color: #17a2b8; color: white; border: 1px solid #117a8b;">🔑 Trocar Senha</a>
                <a href="/sso_sigef" class="btn-nav-sigef">🔗 SIGEF</a>
                <a href="/logout" class="logout">🚪 Sair</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

<div class="container">
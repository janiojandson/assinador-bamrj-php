<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Assinador BAMRJ</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f4f9; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-box { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
            border-top: 5px solid #002244; 
            width: 100%; 
            max-width: 350px; 
        }
        .login-box h2 { color: #002244; text-align: center; margin: 0 0 5px 0; }
        .login-box p { text-align: center; color: #666; margin-bottom: 25px; font-size: 0.9em; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 0.9em; font-weight: bold;}
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; font-size: 0.9em; }
        input { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #002244; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1.1em; transition: 0.2s; }
        button:hover { filter: brightness(1.2); }
        .footer-text { text-align: center; font-size: 0.8em; color: #888; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Assinador-BAMRJ</h2>
        <p>Sistema de Tramitação e Assinatura</p>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <label>Usuário:</label>
            <input type="text" name="username" required autocomplete="off">
            
            <label>Senha:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Entrar no Sistema</button>
        </form>

        <p class="footer-text">Acesso restrito a militares e servidores autorizados.</p>
    </div>

</body>
</html>
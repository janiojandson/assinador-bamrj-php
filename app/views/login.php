<?php
$auth = new \App\Controllers\AuthController();
$error = $auth->login();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Assinador BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css"> 
</head>
<body style="background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center;">
        <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="width: 100px; margin-bottom: 20px;">
        <h2>Assinador BAMRJ</h2>
        
        <?php if ($error): ?>
            <p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Utilizador Militar" required 
                   style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            <input type="password" name="password" placeholder="Senha" required 
                   style="width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
            
            <button type="submit" style="width: 100%; padding: 12px; background-color: #00447c; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; margin-bottom: 15px;">
                ENTRAR
            </button>
        </form>

        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        
        <a href="/acesso_publico" style="display: block; width: 100%; padding: 12px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; box-sizing: border-box;">
            🔍 Consulta Pública (Sem Senha)
        </a>
    </div>
</body>
</html>
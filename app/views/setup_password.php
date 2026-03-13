<?php
// Lógica de Processamento
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 6) {
        $error = "A nova senha deve ter pelo menos 6 caracteres.";
    } elseif ($new_password !== $confirm_password) {
        $error = "As senhas não coincidem.";
    } else {
        // Chamada ao Model que criámos na Fase 2
        if (\App\Models\User::updatePassword($_SESSION['user_id'], $new_password)) {
            $success = "Senha atualizada com sucesso! Redirecionando...";
            // Atualiza a sessão para refletir que não precisa mais mudar a senha
            $_SESSION['must_change_password'] = false;
            header("Refresh: 2; url=/index");
        } else {
            $error = "Erro ao atualizar a senha no banco de dados.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurar Senha - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body style="background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    
    <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="width: 80px;">
            <h2 style="color: #00447c;">Primeiro Acesso</h2>
            <p style="font-size: 0.9em; color: #666;">Por razões de segurança militar, deve alterar a sua senha inicial.</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.85em;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #dcfce7; color: #15803d; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.85em;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nova Senha:</label>
                <input type="password" name="new_password" required minlength="6" autofocus>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Confirmar Nova Senha:</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px;">
                ATUALIZAR SENHA
            </button>
        </form>
    </div>

</body>
</html>
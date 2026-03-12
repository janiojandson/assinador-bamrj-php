<?php
namespace App\Controllers;

use App\Core\Database;

class SetupPasswordController {
    
    public function index() {
        // Blindagem: Só entra aqui se estiver logado e se precisar MESMO mudar a senha
        if (!isset($_SESSION['user_id']) || $_SESSION['must_change_password'] == false) {
            header("Location: /dashboard");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (strlen($new_password) < 6) {
                $error = "Aviso Tático: A nova senha deve ter pelo menos 6 caracteres.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Aviso Tático: As senhas não coincidem. Tente novamente.";
            } else {
                // Atualiza a senha no cofre (PostgreSQL) e liberta o acesso
                $db = Database::getConnection();
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $db->prepare("UPDATE users SET password_hash = :hash, must_change_password = FALSE WHERE id = :id");
                $stmt->execute([
                    'hash' => $hash,
                    'id' => $_SESSION['user_id']
                ]);

                // Atualiza a sessão e envia para o Dashboard
                $_SESSION['must_change_password'] = false;
                header("Location: /dashboard");
                exit;
            }
        }

        require __DIR__ . '/../Views/setup_password.php';
    }
}
?>
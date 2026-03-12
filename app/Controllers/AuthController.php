<?php
namespace App\Controllers;

use App\Core\Database;

class AuthController {
    
    // Renderiza a página de login ou processa a submissão
    public function login() {
        // Se a requisição for POST (submissão do formulário)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = "Preencha todos os campos.";
                require __DIR__ . '/../Views/login.php';
                return;
            }

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Verifica a senha e inicia a sessão
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['must_change_password'] = $user['must_change_password'];

                // Roteamento Tático: Se precisa mudar a senha, vai para o setup
                if ($user['must_change_password']) {
                    header("Location: /setup-password");
                } else {
                    header("Location: /dashboard");
                }
                exit;
            } else {
                $error = "Credenciais inválidas. Acesso negado.";
            }
        }
        
        // Se for GET (apenas aceder à página), renderiza a View
        require __DIR__ . '/../Views/login.php';
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: /login");
        exit;
    }
}
?>
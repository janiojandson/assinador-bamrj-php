<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class AuthController {
    
    // 🔐 Motor de Login Padrão
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Monta a Sessão Tática
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['must_change_password'] = $user['must_change_password'];

                // Trava Direcionadora
                if ($user['must_change_password']) {
                    header("Location: /setup_password");
                } else {
                    header("Location: /index");
                }
                exit();
            }
            return "Credenciais de acesso inválidas.";
        }
        return null;
    }

    // 🛡️ Motor de Configuração de Senha
    public function setupPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // 1. Verificações de Segurança
            if (empty($new_password) || empty($confirm_password)) {
                return "Preencha todos os campos.";
            }

            if ($new_password !== $confirm_password) {
                return "As senhas digitadas não coincidem.";
            }

            if (strlen($new_password) < 3) {
                return "A senha deve conter no mínimo 3 caracteres por segurança.";
            }

            // 2. Criptografia e Gravação no Banco
            $db = Database::getConnection();
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $user_id = $_SESSION['user_id'];

            $stmt = $db->prepare("UPDATE users SET password_hash = ?, must_change_password = FALSE WHERE id = ?");
            $stmt->execute([$hash, $user_id]);

            // 3. Liberação da Trava na Sessão Atual
            $_SESSION['must_change_password'] = false;

            // 4. Salto Tático para o Dashboard
            header("Location: /index");
            exit();
        }
        return null;
    }

    // 🚀 NOVO: Motor de Geração de Token SSO para o SIGEF
    public function redirectToSigef() {
        if (!isset($_SESSION['username'])) {
            header("Location: /login");
            exit();
        }

        $username = $_SESSION['username'];
        $timestamp = time(); // Hora exata do clique
        
        // ⚠️ CHAVE SECRETA TÁTICA (Deve ser exatamente a mesma no sso_login.php do SIGEF)
        $secret_key = 'BAMRJ_SIGEF_SECURE_TOKEN_2026'; 
        
        // Cria a Assinatura Criptográfica: (NIP + Tempo + Chave Secreta)
        $token = hash('sha256', $username . $timestamp . $secret_key);
        
        // URL de destino do SIGEF (Mude para a URL ou IP do seu servidor SIGEF em produção)
        $sigef_url = "http://localhost:8000/sso_login.php"; 
        
        // Monta o Link de Salto
        $redirect_url = $sigef_url . "?user=" . urlencode($username) . "&time=" . $timestamp . "&token=" . $token;
        
        // Executa o Salto Tático
        header("Location: " . $redirect_url);
        exit();
    }
}
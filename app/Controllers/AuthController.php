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
                
                // 🔄 Carrega o estado do substituto do BD (fonte de verdade)
                $_SESSION['is_substitute'] = (bool)($user['substituto_ativo'] ?? false);

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

    // 🛡️ Motor de Configuração de Senha — SEM restrições de tamanho ou complexidade
    public function setupPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // 1. Verificações Mínimas (apenas não-vazio e coincidência)
            if (empty($new_password) || empty($confirm_password)) {
                return "Preencha todos os campos.";
            }

            if ($new_password !== $confirm_password) {
                return "As senhas digitadas não coincidem.";
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

    /**
     * 🔑 Trocar Senha — Disponível no menu do utilizador logado
     * SEM restrições de tamanho ou complexidade
     */
    public function trocarSenha() {
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha_atual = $_POST['senha_atual'] ?? '';
            $nova_senha = $_POST['nova_senha'] ?? '';
            $confirma_senha = $_POST['confirma_senha'] ?? '';
            
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($senha_atual, $user['password_hash'])) {
                $error = "A senha atual está incorreta.";
            } elseif (empty($nova_senha)) {
                $error = "A nova senha não pode estar vazia.";
            } elseif ($nova_senha !== $confirma_senha) {
                $error = "A nova senha e a confirmação não coincidem.";
            } else {
                $hash = password_hash($nova_senha, PASSWORD_BCRYPT);
                $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                   ->execute([$hash, $_SESSION['user_id']]);
                $success = "Senha alterada com sucesso!";
            }
        }
        
        require __DIR__ . '/../views/trocar_senha.php';
    }

    // 🚀 Motor de Geração de Token SSO para o SIGEF
    public function redirectToSigef() {
        if (!isset($_SESSION['username'])) {
            header("Location: /login");
            exit();
        }

        $username = $_SESSION['username'];
        $timestamp = time();
        
        // ⚠️ CHAVE SECRETA TÁTICA (Deve ser a mesma nos dois sistemas)
        $secret_key = getenv('SSO_SECRET_KEY') ?: 'BAMRJ_SSO_SECRET_2024';
        
        // Gera o token HMAC-SHA256
        $token_data = $username . '|' . $timestamp;
        $signature = hash_hmac('sha256', $token_data, $secret_key);
        $token = base64_encode($token_data . '|' . $signature);
        
        // URL do SIGEF com o token
        $sigef_url = getenv('SIGEF_URL') ?: 'https://sigef-bamrj.up.railway.app';
        $redirect_url = $sigef_url . '/sso_return?token=' . urlencode($token);
        
        header("Location: " . $redirect_url);
        exit();
    }

    // 🔙 Motor de Recebimento de Token SSO do Assinador
    public function loginFromSigef() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            header("Location: /login");
            exit();
        }
        
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);
        
        if (count($parts) !== 3) {
            header("Location: /login");
            exit();
        }
        
        $username = $parts[0];
        $timestamp = (int)$parts[1];
        $signature = $parts[2];
        
        // Verifica se o token não expirou (5 minutos)
        if (time() - $timestamp > 300) {
            header("Location: /login");
            exit();
        }
        
        // Verifica a assinatura
        $secret_key = getenv('SSO_SECRET_KEY') ?: 'BAMRJ_SSO_SECRET_2024';
        $expected_signature = hash_hmac('sha256', $username . '|' . $timestamp, $secret_key);
        
        if (!hash_equals($expected_signature, $signature)) {
            header("Location: /login");
            exit();
        }
        
        // Login automático
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['must_change_password'] = $user['must_change_password'];
            $_SESSION['is_substitute'] = (bool)($user['substituto_ativo'] ?? false);
            
            if ($user['must_change_password']) {
                header("Location: /setup_password");
            } else {
                header("Location: /index");
            }
            exit();
        }
        
        header("Location: /login");
        exit();
    }
}
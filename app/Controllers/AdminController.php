<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class AdminController {
    
    // 🛡️ Trava de Segurança Reutilizável
    private function checkAdminAccess() {
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            http_response_code(403);
            die("Acesso Negado: Privilégios insuficientes no perímetro do Assinador-BAMRJ.");
        }
    }

    public function createUser() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Operador';

            $db = Database::getConnection();
            
            // Verificação de duplicidade
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                die("<h1>Erro Crítico</h1><p>Este utilizador já existe na base de dados. Volte e tente outro nome.</p>");
            }

            // Geração de Hash seguro
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Por padrão, a trava 'must_change_password' é ativada no INSERT
            $sql = "INSERT INTO users (name, username, password_hash, role, must_change_password) VALUES (?, ?, ?, ?, TRUE)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $username, $hash, $role]);

            header("Location: /index");
            exit();
        }
    }

    public function editUser() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_POST['user_id'] ?? 0;
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? ''; 

            $db = Database::getConnection();
            
            // Se o Admin decidir resetar a senha, reativamos a trava de troca obrigatória
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET role = ?, password_hash = ?, must_change_password = TRUE WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$role, $hash, $user_id]);
            } else {
                $sql = "UPDATE users SET role = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$role, $user_id]);
            }

            header("Location: /index");
            exit();
        }
    }

    public function deleteUser($id) {
        $this->checkAdminAccess();

        $db = Database::getConnection();
        
        // Bloqueio tático: Impede a exclusão do Administrador Master
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && $user['username'] !== 'admin') {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: /index");
        exit();
    }
}
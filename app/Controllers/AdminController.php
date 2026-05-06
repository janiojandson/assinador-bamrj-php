<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class AdminController {
    
    // 🛡️ Trava de Segurança Reutilizável
    private function checkAdminAccess() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (($_SESSION['role'] ?? '') !== 'Admin') {
            http_response_code(403);
            die("Acesso Negado: Privilégios insuficientes no perímetro do Assinador-BAMRJ.");
        }
    }

    // 📋 Lista utilizadores para desenhar a tabela no admin_users.php
    public function listUsers() {
        $this->checkAdminAccess();
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, username, role, must_change_password, substituto_ativo FROM users ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ➕ Cria novo utilizador — SEM restrições de senha
    public function handleCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            $this->checkAdminAccess();
            $name = $_POST['name'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Operador';

            if (empty($password)) {
                die("<script>alert('A senha não pode estar vazia.'); history.back();</script>");
            }

            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) die("<h1>Erro Crítico</h1><p>Este utilizador já existe.</p><a href='/admin'>Voltar</a>");

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (name, username, password_hash, role, must_change_password, substituto_ativo) VALUES (?, ?, ?, ?, TRUE, FALSE)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $username, $hash, $role]);

            header("Location: /admin");
            exit();
        }
    }

    // ✏️ Edita o perfil ou senha do utilizador — SEM restrições de senha
    public function handleEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
            $this->checkAdminAccess();
            $user_id = $_POST['user_id'] ?? 0;
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? ''; 

            $db = Database::getConnection();
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
            header("Location: /admin");
            exit();
        }
    }

    // 🔄 Tática de Atualização: Roda comandos SQL direto pelo painel web
    public function handleMigration() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'migrate_db') {
            $this->checkAdminAccess();
            $db = Database::getConnection();
            
            try {
                $db->beginTransaction();
                
                $migracoes = [
                    "ALTER TABLE users ADD COLUMN IF NOT EXISTS substituto_ativo BOOLEAN DEFAULT FALSE;",
                    "ALTER TABLE users ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN DEFAULT TRUE;",
                ];
                
                foreach ($migracoes as $sql) {
                    $db->exec($sql);
                }
                
                $db->commit();
                die("<script>alert('✅ Migrações aplicadas com sucesso!'); location.href='/admin';</script>");
            } catch (\Exception $e) {
                $db->rollBack();
                die("<script>alert('❌ Erro na migração: " . addslashes($e->getMessage()) . "'); history.back();</script>");
            }
        }
    }
}
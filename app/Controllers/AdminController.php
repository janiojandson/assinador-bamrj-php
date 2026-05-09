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

    // 🩹 TRANSPLANTE LEGADO: Funcionalidades de gestão de utilizadores e manutenção

    // ❌ Elimina o utilizador
    public function deleteUser($id) {
        $this->checkAdminAccess();
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        // Blindagem para não apagar o próprio admin
        if ($user && $user['username'] !== 'admin') {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: /admin");
        exit();
    }

    // ==========================================
    // ⚠️ ZONA DE PERIGO (Lógica de Limpeza)
    // ==========================================

    // Função auxiliar: Apaga todos os PDFs físicos
    private function clearUploadsFolder() {
        $dir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($dir)) return;
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            if ($fileinfo->getFilename() === '.gitkeep') continue;
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
    }

    // 🧹 Limpa apenas processos e arquivos (WIPE DADOS)
    public function resetDocs() {
        $this->checkAdminAccess();
        $db = Database::getConnection();
        $db->exec("TRUNCATE TABLE documents RESTART IDENTITY CASCADE");
        $this->clearUploadsFolder();
        header("Location: /admin");
        exit();
    }

    // 💣 Formatação Total do Sistema (FACTORY RESET)
    public function factoryReset() {
        $this->checkAdminAccess();
        $db = Database::getConnection();
        try {
            $db->exec("DROP TABLE IF EXISTS document_files CASCADE;");
            $db->exec("DROP TABLE IF EXISTS events CASCADE;");
            $db->exec("DROP TABLE IF EXISTS documents CASCADE;");
            $db->exec("DROP TABLE IF EXISTS users CASCADE;");
            $this->clearUploadsFolder();

            $db->exec("
                CREATE TABLE users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(128) NOT NULL,
                    username VARCHAR(64) UNIQUE NOT NULL,
                    password_hash VARCHAR(256) NOT NULL,
                    role VARCHAR(64) NOT NULL,
                    substituto_ativo BOOLEAN DEFAULT FALSE,
                    must_change_password BOOLEAN DEFAULT TRUE
                );
                CREATE TABLE documents (
                    id SERIAL PRIMARY KEY,
                    protocol VARCHAR(32) UNIQUE NOT NULL,
                    name VARCHAR(128) NOT NULL,
                    cpf_cnpj VARCHAR(20),
                    solemp VARCHAR(50),
                    status VARCHAR(64) DEFAULT 'Caixa de Entrada - Gestor Financeiro',
                    is_priority BOOLEAN DEFAULT FALSE,
                    current_observation TEXT,
                    uploader_name VARCHAR(64),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE events (
                    id SERIAL PRIMARY KEY,
                    document_id INTEGER REFERENCES documents(id) ON DELETE CASCADE,
                    user_name VARCHAR(64),
                    action VARCHAR(64) NOT NULL,
                    observation TEXT,
                    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE document_files (
                    id SERIAL PRIMARY KEY,
                    document_id INTEGER REFERENCES documents(id) ON DELETE CASCADE,
                    filename VARCHAR(256) NOT NULL,
                    file_type VARCHAR(64) NOT NULL
                );
            ");

            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, username, password_hash, role, must_change_password) VALUES (?, ?, ?, ?, false)");
            $stmt->execute(['Administrador', 'admin', $hash, 'Admin']);

            if (session_status() === PHP_SESSION_ACTIVE) session_destroy();

            echo "<div style='background:#28a745;color:white;padding:20px;font-family:sans-serif;text-align:center;margin-top:50px;border-radius:8px;max-width:600px;margin-left:auto;margin-right:auto;'>
                    <h1>✅ Sistema formatado com sucesso!</h1>
                    <p>As tabelas foram reconstruídas e os ficheiros PDF apagados. O utilizador <b>admin</b> com a senha <b>admin123</b> foi restaurado.</p>
                    <br>
                    <a href='/login' style='background:white;color:#28a745;padding:10px 20px;text-decoration:none;font-weight:bold;border-radius:4px;'>Fazer Login</a>
                  </div>";
        } catch (\Exception $e) {
            echo "<div style='background:#dc3545;color:white;padding:20px;font-family:sans-serif;'>
                    <h1>⚠️ Falha na Criação Estrutural</h1>
                    <p>" . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
}
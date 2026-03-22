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
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) die("<h1>Erro Crítico</h1><p>Este utilizador já existe.</p>");

            $hash = password_hash($password, PASSWORD_BCRYPT);
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

    // ==========================================
    // ⚠️ ZONA DE PERIGO (Lógica de Limpeza)
    // ==========================================

    // Função auxiliar: Apaga todos os PDFs e Pastas de testes
    private function clearUploadsFolder() {
        $dir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($dir)) return;
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
    }

    // 🧹 NOVA ROTA: Limpa apenas processos e arquivos (mantém usuários)
    public function resetDocuments() {
        $this->checkAdminAccess();
        $db = Database::getConnection();
        
        // TRUNCATE com CASCADE limpa as tabelas de documentos, arquivos e eventos instantaneamente
        $db->exec("TRUNCATE TABLE documents RESTART IDENTITY CASCADE");
        
        // Apaga os PDFs físicos
        $this->clearUploadsFolder();
        
        header("Location: /index");
        exit();
    }

    // 💣 ROTA SECRETA: Construtor do Banco de Dados / Factory Reset
    public function resetDatabase() {
        $this->checkAdminAccess(); // Adicionado para blindar a rota
        $db = Database::getConnection();
        try {
            // 1. Destruição Tática (Limpa tudo no DB)
            $db->exec("DROP TABLE IF EXISTS document_files CASCADE;");
            $db->exec("DROP TABLE IF EXISTS events CASCADE;");
            $db->exec("DROP TABLE IF EXISTS documents CASCADE;");
            $db->exec("DROP TABLE IF EXISTS users CASCADE;");

            // 2. Destruição de Arquivos Físicos (NOVO)
            $this->clearUploadsFolder();

            // 3. Reconstrução Estrutural
            $db->exec("
                CREATE TABLE users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(128) NOT NULL,
                    username VARCHAR(64) UNIQUE NOT NULL,
                    password_hash VARCHAR(256) NOT NULL,
                    role VARCHAR(64) NOT NULL,
                    must_change_password BOOLEAN DEFAULT TRUE
                );

                CREATE TABLE documents (
                    id SERIAL PRIMARY KEY,
                    protocol VARCHAR(32) UNIQUE NOT NULL,
                    name VARCHAR(128) NOT NULL,
                    cpf_cnpj VARCHAR(20),
                    solemp VARCHAR(50),
                    status VARCHAR(64) DEFAULT 'Caixa de Entrada - Enc. Finanças',
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

            // 4. Criação do Master Admin (Senha admin123)
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, username, password_hash, role, must_change_password) VALUES (?, ?, ?, ?, false)");
            $stmt->execute(['Administrador', 'admin', $hash, 'Admin']);

            // 5. Destrói a sessão atual para forçar login com o novo DB limpo
            session_destroy();

            echo "<div style='background:#28a745;color:white;padding:20px;font-family:sans-serif;'>
                    <h1>✅ Senhor! Sistema formatado com sucesso!</h1>
                    <p>As tabelas foram reconstruídas e os ficheiros PDF apagados. O utilizador <b>admin</b> com a senha <b>admin123</b> foi restaurado.</p>
                    <a href='/login' style='color:white;text-decoration:underline;font-weight:bold;'>Clique aqui para fazer Login</a>
                  </div>";
        } catch (\Exception $e) {
            echo "<div style='background:#dc3545;color:white;padding:20px;font-family:sans-serif;'>
                    <h1>⚠️ Falha na Criação Estrutural</h1>
                    <p>" . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
}
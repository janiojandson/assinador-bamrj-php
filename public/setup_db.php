<?php
// public/setup_db.php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    // Liga ao banco de dados usando a nossa classe PDO
    $db = \App\Core\Database::getConnection();

    // O nosso Script Tático em SQL
    $sql = "
        -- 1. Tabela de Utilizadores
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL,
            must_change_password BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- 2. Tabela de Documentos/Processos
        CREATE TABLE IF NOT EXISTS documents (
            id SERIAL PRIMARY KEY,
            protocol VARCHAR(50) UNIQUE NOT NULL,
            subject VARCHAR(255) NOT NULL,
            cpf_cnpj VARCHAR(20),
            solemp VARCHAR(50),
            status VARCHAR(50) NOT NULL,
            is_priority BOOLEAN DEFAULT FALSE,
            created_by INTEGER REFERENCES users(id) ON DELETE RESTRICT,
            current_owner INTEGER REFERENCES users(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- 3. Tabela de Ficheiros Físicos
        CREATE TABLE IF NOT EXISTS document_files (
            id SERIAL PRIMARY KEY,
            document_id INTEGER REFERENCES documents(id) ON DELETE CASCADE,
            file_path VARCHAR(255) NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- 4. Tabela de Auditoria (Eventos)
        CREATE TABLE IF NOT EXISTS events (
            id SERIAL PRIMARY KEY,
            document_id INTEGER REFERENCES documents(id) ON DELETE CASCADE,
            user_id INTEGER REFERENCES users(id) ON DELETE RESTRICT,
            action VARCHAR(100) NOT NULL,
            observation TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- 5. Injeção do Utilizador Admin (Apenas se não existir para evitar erros)
        INSERT INTO users (username, password_hash, name, role, must_change_password)
        SELECT 'admin', '$2y$10$Ew2P7G0/1/xT1iHnU9D.OeQh4P./4Q8Q6eX.7.1v.Q4.4.4.4.4.4', 'Administrador do Sistema', 'Admin', TRUE
        WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
    ";

    // Executa o Comando
    $db->exec($sql);

    echo "<div style='font-family: Arial; padding: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h1>✅ Operação Tática Concluída!</h1>";
    echo "<p>As tabelas do Assinador BAMRJ foram criadas com sucesso no PostgreSQL.</p>";
    echo "<p><strong>Aviso de Segurança:</strong> Por favor, apague este ficheiro (<code>setup_db.php</code>) do seu VSCode e faça um novo push para o GitHub para evitar que terceiros resetem o banco de dados.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family: Arial; padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h1>❌ Falha na Manobra</h1>";
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se as Variáveis de Ambiente (PGHOST, PGDATABASE, etc.) estão corretas no painel do Railway.</p>";
    echo "</div>";
}
?>
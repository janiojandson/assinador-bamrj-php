<?php
/**
 * Script de Migração Automática — Assinador BAMRJ
 * Executa as migrações SQL necessárias para as novas features.
 * ACESSO RESTRITO: Remove este ficheiro após a execução!
 */
require __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

echo "<h1>🔄 Migração do Assinador BAMRJ</h1><pre>";

try {
    $db = Database::getConnection();
    
    $migrations = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS substituto_ativo BOOLEAN DEFAULT FALSE",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS must_change_password BOOLEAN DEFAULT TRUE",
    ];
    
    foreach ($migrations as $i => $sql) {
        try {
            $db->exec($sql);
            echo "✅ Migração " . ($i + 1) . ": OK\n";
        } catch (PDOException $e) {
            echo "⚠️ Migração " . ($i + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Todas as migrações foram processadas!\n";
    echo "⚠️ DELETE este ficheiro (migrate.php) do servidor após a execução.\n";
    
} catch (Exception $e) {
    echo "❌ Erro crítico: " . $e->getMessage() . "\n";
}

echo "</pre>";
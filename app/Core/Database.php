<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            require_once __DIR__ . '/../../config/config.php';
            
            try {
                // Ligação nativa ao PostgreSQL
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Segurança contra SQL Injection
                ]);
            } catch (PDOException $e) {
                // Em produção, deve-se registar o erro num ficheiro de log, não exibir no ecrã
                die("Erro de Ligação à Base de Dados. Contacte o Administrador.");
            }
        }
        return self::$instance;
    }
}
?>
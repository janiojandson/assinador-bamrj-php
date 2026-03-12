<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            // No Railway, as variáveis virão do ambiente automaticamente
            $host = getenv('PGHOST');
            $db   = getenv('PGDATABASE');
            $user = getenv('PGUSER');
            $pass = getenv('PGPASSWORD');
            $port = getenv('PGPORT');

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                error_log("Erro de Conexão: " . $e->getMessage());
                die("Falha na comunicação com a Base de Dados Militar.");
            }
        }
        return self::$instance;
    }
}
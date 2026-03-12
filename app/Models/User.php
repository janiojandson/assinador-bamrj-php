<?php
namespace App\Models;

use App\Core\Database;

class User {
    public static function authenticate($username, $password) {
        $db = Database::getConnection();
        
        // Prepared Statement (Blindagem contra SQL Injection)
        $stmt = $db->prepare("SELECT id, name, username, password_hash, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        
        $user = $stmt->fetch();
        
        // Verificação nativa do hash no PHP
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
    
    public static function createPasswordHash($password) {
        // Usa o algoritmo BCRYPT por defeito
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
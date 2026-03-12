<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    /**
     * Procura um utilizador pelo username (Tradução de User.query.filter_by)
     */
    public static function findByUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Verifica a senha usando o padrão do sistema (Tradução de check_password_hash)
     */
    public static function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Define uma nova senha com hash seguro (Tradução de generate_password_hash)
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Atualiza a senha e remove a trava de troca obrigatória
     */
    public static function updatePassword($userId, $newPassword) {
        $db = Database::getConnection();
        $hash = self::hashPassword($newPassword);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, must_change_password = FALSE WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    }
}
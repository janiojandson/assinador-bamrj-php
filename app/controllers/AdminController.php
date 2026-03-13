<?php
namespace App\Controllers;

use App\Core\Database;
use App\Models\User;
use PDO;

class AdminController {
    public function listUsers() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, username, role, must_change_password FROM users ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function handleCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
            $db = Database::getConnection();
            $name = $_POST['name'];
            $username = $_POST['username'];
            $password = User::hashPassword($_POST['password']);
            $role = $_POST['role'];

            $stmt = $db->prepare("INSERT INTO users (name, username, password_hash, role, must_change_password) VALUES (?, ?, ?, ?, TRUE)");
            $stmt->execute([$name, $username, $password, $role]);
            header("Location: /admin");
            exit();
        }
    }

    public function handleEdit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
            $db = Database::getConnection();
            $id = $_POST['user_id'];
            $role = $_POST['role'];
            
            if (!empty($_POST['password'])) {
                $password = User::hashPassword($_POST['password']);
                $stmt = $db->prepare("UPDATE users SET role = ?, password_hash = ?, must_change_password = TRUE WHERE id = ?");
                $stmt->execute([$role, $password, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $id]);
            }
            header("Location: /admin");
            exit();
        }
    }

    public function deleteUser($id) {
        $db = Database::getConnection();
        // Impede a remoção do admin principal por segurança
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND username != 'admin'");
        $stmt->execute([$id]);
        header("Location: /admin");
        exit();
    }
}
<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    public function login() {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::authenticate($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['must_change_password'] = $user['must_change_password'];

                header("Location: /index");
                exit();
            } else {
                $error = "Usuário ou senha inválidos.";
            }
        }
        return $error;
    }
}
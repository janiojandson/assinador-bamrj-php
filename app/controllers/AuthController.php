<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = User::findByUsername($username);

            if ($user && User::verifyPassword($password, $user['password_hash'])) {
                // Injeta os dados na sessão (Tradução de session.update do Python)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Regra de Negócio: Trava de troca de senha
                if ($user['must_change_password']) {
                    header("Location: /setup_password");
                    exit();
                }

                header("Location: /index");
                exit();
            } else {
                return "Utilizador ou senha inválidos.";
            }
        }
        return null;
    }
}
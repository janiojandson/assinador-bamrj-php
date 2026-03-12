<?php
namespace App\Controllers;

class MainController {
    public function dashboard() {
        // Verifica se o utilizador está logado, senão expulsa para o login
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        // Apenas uma mensagem de sucesso para validar que o Login funcionou!
        echo "<h1 style='color: #002244; font-family: Arial;'>Bem-vindo, " . $_SESSION['name'] . "!</h1>";
        echo "<p style='font-family: Arial;'>Seu perfil é: <strong>" . $_SESSION['role'] . "</strong></p>";
        echo "<a href='/logout' style='color: red; font-family: Arial;'>Sair (Logout)</a>";
    }
}
?>
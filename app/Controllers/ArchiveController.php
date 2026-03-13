<?php
namespace App\Controllers;

class ArchiveController {
    
    // 🛡️ Tática de Infiltração: Cria uma sessão sem senha para Consulta Pública
    public static function simulatePublicAccess() {
        // Limpa qualquer vestígio de logins anteriores
        session_unset();
        session_destroy();
        session_start();
        
        // Forja as credenciais do Usuário Comum
        $_SESSION['user_id'] = 0; // ID 0 não existe no banco, então não edita nada
        $_SESSION['username'] = 'consulta_publica';
        $_SESSION['name'] = 'Consulta Pública';
        $_SESSION['role'] = 'Usuário Comum';
        $_SESSION['must_change_password'] = false;
        
        // Redireciona para o arquivo
        header("Location: /arquivo");
        exit();
    }
}
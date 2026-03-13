<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class DocumentController {
    public function getDashboardData() {
        $db = Database::getConnection();
        $role = $_SESSION['role'];
        $is_sub = $_SESSION['is_substitute'] ?? false;
        
        // Lógica de filtragem por Role (Baseada no routes.py original)
        $inbox_statuses = [];
        
        if ($role === 'Operador') {
            // Operadores veem tudo que não está arquivado/cancelado
            $stmt = $db->prepare("SELECT * FROM documents WHERE status NOT IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado') ORDER BY is_priority DESC, created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        // Definição das caixas de entrada por hierarquia militar
        if ($role === 'Enc_Financas' || $role === 'Ajudante_Encarregado') {
            $inbox_statuses = ['Caixa de Entrada - Enc. Finanças'];
        } elseif ($role === 'Chefe_Departamento') {
            $inbox_statuses = ['Caixa de Entrada - Chefe'];
            if ($is_sub) $inbox_statuses[] = 'Caixa de Entrada - Vice-Diretor';
        } elseif ($role === 'Vice_Diretor') {
            $inbox_statuses = ['Caixa de Entrada - Vice-Diretor'];
            if ($is_sub) $inbox_statuses[] = 'Caixa de Entrada - Diretor';
        } elseif ($role === 'Diretor') {
            $inbox_statuses = ['Caixa de Entrada - Diretor'];
        }

        if (empty($inbox_statuses)) return [];

        $inQuery = implode(',', array_fill(0, count($inbox_statuses), '?'));
        $stmt = $db->prepare("SELECT * FROM documents WHERE status IN ($inQuery) ORDER BY is_priority DESC, created_at DESC");
        $stmt->execute($inbox_statuses);
        return $stmt->fetchAll();
    }
}
<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class ArchiveController {
    public function getArchiveData() {
        $db = Database::getConnection();
        $role = $_SESSION['role'] ?? 'Usuário Comum';
        $search = $_GET['q'] ?? '';
        $year = $_GET['ano'] ?? date('Y');
        
        // Base da Query: Apenas processos finalizados
        $sql = "SELECT * FROM documents WHERE status IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado') AND EXTRACT(YEAR FROM created_at) = :year";
        $params = ['year' => $year];

        if (!empty($search)) {
            $cleanSearch = preg_replace('/\D/', '', $search);
            $sql .= " AND (name ILIKE :search OR protocol ILIKE :search OR cpf_cnpj LIKE :clean OR solemp LIKE :clean)";
            $params['search'] = "%$search%";
            $params['clean'] = "%$cleanSearch%";
        } else {
            // REGRA DE NEGÓCIO: Se for Usuário Comum e não houver pesquisa, a lista fica vazia (Blindagem)
            if ($role === 'Usuário Comum') return [];
        }

        $stmt = $db->prepare($sql . " ORDER BY created_at DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Simulação do Acesso Público sem Login
    public static function simulatePublicAccess() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'consulta_publica';
        $_SESSION['name'] = 'Consulta Pública (LGPD)';
        $_SESSION['role'] = 'Usuário Comum';
        header("Location: /arquivo");
        exit();
    }
}
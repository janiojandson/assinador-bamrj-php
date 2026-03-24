<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;
use Exception;

class ArchiveController {
    
    // 🛡️ Tática de Infiltração: Cria uma sessão sem senha para Consulta Pública
    public static function simulatePublicAccess() {
        session_unset();
        session_destroy();
        session_start();
        
        $_SESSION['user_id'] = 0; // Usuário Fantasma
        $_SESSION['username'] = 'consulta_publica';
        $_SESSION['name'] = 'Consulta Pública';
        $_SESSION['role'] = 'Usuário Comum';
        $_SESSION['must_change_password'] = false;
        
        header("Location: /arquivo");
        exit();
    }

    // 🗄️ Motor de Busca do Arquivo Geral
    public function getArchiveData(): array {
        $data = [
            'role' => $_SESSION['role'] ?? '',
            'search_query' => $_GET['q'] ?? '',
            'documents' => []
        ];

        try {
            $db = Database::getConnection();
        } catch (Exception $e) {
            return $data; // Retorna vazio se falhar a Base de Dados
        }

        $search_query = trim($_GET['q'] ?? '');
        $search_query_clean = preg_replace('/\D/', '', $search_query);
        $ano_filtro = $_GET['ano'] ?? date('Y');

        $data['search_query'] = $search_query;

        // 🟢 ATUALIZAÇÃO (Ponto 6): Calculando a data/hora da última movimentação (last_update)
        $sql = "SELECT d.*, 
                COALESCE((SELECT MAX(timestamp) FROM events e WHERE e.document_id = d.id), d.created_at) as last_update 
                FROM documents d WHERE 1=1";
        $params = [];

        // Ignora o filtro de ano se for "todos"
        if ($ano_filtro !== 'todos') {
            $sql .= " AND EXTRACT(YEAR FROM d.created_at) = ?";
            $params[] = $ano_filtro;
        }

        if (!empty($search_query)) {
            // Se houver pesquisa, procura em QUALQUER etapa/status (Pesquisa Global)
            $sql .= " AND (d.name ILIKE ? OR d.protocol ILIKE ? OR d.cpf_cnpj ILIKE ? OR d.solemp ILIKE ?)";
            $like_q = "%{$search_query}%";
            $like_clean = "%{$search_query_clean}%";
            array_push($params, $like_q, $like_q, $like_clean, $like_clean);
        } else {
            // Se NÃO houver pesquisa (Navegação Padrão do Menu Arquivo)
            if ($data['role'] === 'Usuário Comum') {
                // Público não vê lista geral (Blindagem LGPD)
                return $data; 
            } else {
                // Militar vê apenas processos já finalizados
                $sql .= " AND d.status IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado')";
            }
        }

        // Ordena sempre pela última movimentação (Mais recentes primeiro)
        $sql .= " ORDER BY last_update DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $all_docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data['documents'] = is_array($all_docs) ? $all_docs : [];

        return $data;
    }
}
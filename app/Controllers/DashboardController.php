<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class DashboardController {
    
    public function getDashboardData() {
        $db = Database::getConnection();
        
        $role = $_SESSION['role'] ?? null;
        $is_sub = $_SESSION['is_substitute'] ?? false;
        
        // Regra: Usuário Comum vai direto para o Arquivo
        if ($role === 'Usuário Comum') {
            header("Location: /arquivo");
            exit();
        }

        $search_query = $_GET['q'] ?? '';
        $search_query_clean = preg_replace('/\D/', '', $search_query);
        $ano_filtro = $_GET['ano'] ?? date('Y');

        $data = [
            'role' => $role,
            'is_substitute' => $is_sub,
            'users' => [],
            'documents' => [],
            'pre_protocol' => '',
            'inbox_count' => 0
        ];

        // 1. Visão do Admin
        if ($role === 'Admin') {
            $stmt = $db->query("SELECT * FROM users ORDER BY name ASC");
            $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // 2. Modo de Pesquisa Ativo
        if (!empty($search_query)) {
            $sql = "SELECT * FROM documents 
                    WHERE EXTRACT(YEAR FROM created_at) = ? 
                    AND (name ILIKE ? OR protocol ILIKE ? OR cpf_cnpj ILIKE ? OR solemp ILIKE ?)
                    ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $like_q = "%{$search_query}%";
            $like_clean = "%{$search_query_clean}%";
            $stmt->execute([$ano_filtro, $like_q, $like_q, $like_clean, $like_clean]);
            $data['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }

        // 3. Visão do Operador
        if ($role === 'Operador') {
            $sql = "SELECT * FROM documents 
                    WHERE status NOT IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado') 
                    ORDER BY is_priority DESC, created_at DESC";
            $stmt = $db->query($sql);
            $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['documents'] = $docs;
            
            // Geração de Pré-protocolo UUID
            $date_str = date('Ymd');
            $uuid = strtoupper(substr(uniqid(), 0, 4));
            $data['pre_protocol'] = "BAMRJ-{$date_str}-{$uuid}";
            
            // Contagem de Inbox Operador
            $inbox_count = 0;
            foreach($docs as $d) {
                if (in_array($d['status'], ['Devolvido - Operador', 'Aguardando Empenho - Operador'])) {
                    $inbox_count++;
                }
            }
            $data['inbox_count'] = $inbox_count;
            return $data;
        }

        // 4. Visão das Chefias (Workflow de Aprovação)
        $inbox_statuses = [];
        if ($role === 'Enc_Financas' || $role === 'Ajudante_Encarregado') {
            $inbox_statuses[] = 'Caixa de Entrada - Enc. Finanças';
        } elseif ($role === 'Chefe_Departamento') {
            $inbox_statuses[] = 'Caixa de Entrada - Chefe';
            if ($is_sub) $inbox_statuses[] = 'Caixa de Entrada - Vice-Diretor';
        } elseif ($role === 'Vice_Diretor') {
            $inbox_statuses[] = 'Caixa de Entrada - Vice-Diretor';
            if ($is_sub) $inbox_statuses[] = 'Caixa de Entrada - Diretor';
        } elseif ($role === 'Diretor') {
            $inbox_statuses[] = 'Caixa de Entrada - Diretor';
        }

        if (!empty($inbox_statuses)) {
            $placeholders = implode(',', array_fill(0, count($inbox_statuses), '?'));
            $sql = "SELECT * FROM documents WHERE status IN ($placeholders) ORDER BY is_priority DESC, created_at ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute($inbox_statuses);
            $data['documents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data['inbox_count'] = count($data['documents']);
        }

        return $data;
    }
}
<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class DocumentController {
    /**
     * Recupera os documentos de acordo com o perfil do militar logado.
     */
    public function getDashboardData() {
        $db = Database::getConnection();
        $role = $_SESSION['role'];
        $is_sub = $_SESSION['is_substitute'] ?? false;
        
        $inbox_statuses = [];
        
        if ($role === 'Operador') {
            $stmt = $db->prepare("SELECT * FROM documents WHERE status NOT IN ('Arquivado', 'Cancelado', 'Anulado', 'Reforçado') ORDER BY is_priority DESC, created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }

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

    /**
     * Processa a aprovação ou rejeição de um documento e registra no histórico.
     */
    public function processAction($docId, $action, $observation) {
        $db = Database::getConnection();
        $role = $_SESSION['role'];
        $is_sub = $_SESSION['is_substitute'] ?? false;
        $username = $_SESSION['username'];

        $stmt = $db->prepare("SELECT status, current_observation FROM documents WHERE id = ?");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();

        if (!$doc) return "Erro: Processo não encontrado.";

        $current_status = $doc['status'];
        $new_status = $current_status;

        // Regra de transição de status militar
        if ($action === 'rejeitar') {
            $new_status = 'Devolvido - Operador';
        } elseif ($action === 'aprovar') {
            if ($current_status === 'Caixa de Entrada - Enc. Finanças') {
                $new_status = 'Caixa de Entrada - Chefe';
            } elseif ($current_status === 'Caixa de Entrada - Chefe') {
                $new_status = ($is_sub && $role === 'Chefe_Departamento') ? 'Caixa de Entrada - Diretor' : 'Caixa de Entrada - Vice-Diretor';
            } elseif ($current_status === 'Caixa de Entrada - Vice-Diretor') {
                $new_status = ($is_sub && $role === 'Vice_Diretor') ? 'Aguardando Empenho - Operador' : 'Caixa de Entrada - Diretor';
            } elseif ($current_status === 'Caixa de Entrada - Diretor') {
                $new_status = 'Aguardando Empenho - Operador';
            }
        }

        $db->beginTransaction();
        try {
            $timestamp = date('d/m H:i');
            $cargo = ($is_sub) ? "$role (SUBSTITUTO)" : $role;
            $new_obs = $doc['current_observation'] . "\n[$timestamp - $cargo]: $observation";

            $update = $db->prepare("UPDATE documents SET status = ?, current_observation = ? WHERE id = ?");
            $update->execute([$new_status, $new_obs, $docId]);

            $event = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, ?, ?)");
            $event->execute([$docId, $username, strtoupper($action), $observation]);

            $db->commit();
            header("Location: /index");
            exit();
        } catch (\Exception $e) {
            $db->rollBack();
            return "Erro na tramitação: " . $e->getMessage();
        }
    }
}
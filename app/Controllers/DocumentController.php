<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class DocumentController {

    private function checkOperador() {
        if (($_SESSION['role'] ?? '') !== 'Operador') {
            http_response_code(403);
            die("Acesso Negado: Apenas Operadores podem manipular documentos de base.");
        }
    }

    public function uploadProcess() {
        $this->checkOperador();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $date_str = date('Ymd');
            $random_hash = strtoupper(bin2hex(random_bytes(3))); 
            $protocol = "BAMRJ-{$date_str}-{$random_hash}";
            
            $name = $_POST['process_name'] ?? '';
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $is_priority = isset($_POST['priority']) ? 1 : 0;
            $obs = $_POST['observation'] ?? '';
            $uploader_name = $_SESSION['username'];
            
            $status = 'Caixa de Entrada - Gestor Financeiro';
            $ano_atual = date('Y');
            
            $upload_dir = __DIR__ . "/../../public/uploads/{$ano_atual}/{$protocol}";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            try {
                $db->beginTransaction();
                $sql = "INSERT INTO documents (protocol, name, cpf_cnpj, solemp, status, is_priority, current_observation, uploader_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";
                $stmt = $db->prepare($sql);
                $stmt->execute([$protocol, $name, $cpf_cnpj, $solemp, $status, $is_priority, "[Início] " . $obs, $uploader_name]);
                $doc_id = $stmt->fetchColumn();

                $this->processarArquivos('minutas', 'Minuta', $doc_id, $ano_atual, $protocol, $upload_dir, $db);
                $this->processarArquivos('anexos', 'Anexo', $doc_id, $ano_atual, $protocol, $upload_dir, $db);

                $db->commit();
                header("Location: /index"); exit();
            } catch (\Exception $e) {
                $db->rollBack(); die("Erro Crítico: " . $e->getMessage());
            }
        }
    }

    private function processarArquivos($inputName, $fileType, $docId, $ano, $protocol, $dir, $db) {
        if (!empty($_FILES[$inputName]['name'][0])) {
            $total = count($_FILES[$inputName]['name']);
            for ($i = 0; $i < $total; $i++) {
                $tmp_name = $_FILES[$inputName]['tmp_name'][$i];
                $name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES[$inputName]['name'][$i]));
                if (move_uploaded_file($tmp_name, "{$dir}/{$name}")) {
                    $stmt = $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, ?)");
                    $stmt->execute([$docId, "uploads/{$ano}/{$protocol}/{$name}", $fileType]);
                }
            }
        }
    }

    /**
     * 🗑️ FASE 4: Cancelar Processo — Operador pode cancelar um processo em trâmite
     * Atualiza o status para 'Cancelado' e interrompe o fluxo de assinaturas
     */
    public function cancelarProcesso() {
        $this->checkOperador();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $doc_id = (int)($_POST['document_id'] ?? 0);
            $motivo = trim($_POST['motivo_cancelamento'] ?? '');
            
            if (empty($doc_id)) {
                die("<script>alert('ID do processo inválido.'); history.back();</script>");
            }
            
            if (empty($motivo)) {
                die("<script>alert('O motivo do cancelamento é OBRIGATÓRIO.'); history.back();</script>");
            }
            
            // Verifica se o processo existe e está em trâmite (não finalizado)
            $stmt = $db->prepare("SELECT id, protocol, status FROM documents WHERE id = ?");
            $stmt->execute([$doc_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doc) {
                die("<script>alert('Processo não encontrado.'); history.back();</script>");
            }
            
            // Não permite cancelar processos já finalizados
            if (in_array($doc['status'], ['Arquivado', 'Cancelado', 'Anulado', 'Reforçado'])) {
                die("<script>alert('Este processo já está finalizado ({$doc['status']}) e não pode ser cancelado.'); history.back();</script>");
            }
            
            $usuario = $_SESSION['username'];
            $timestamp = date('d/m/Y H:i');
            $obs_cancelamento = "[{$timestamp} - Operador]: 🗑️ PROCESSO CANCELADO — Motivo: {$motivo}";
            
            try {
                $db->beginTransaction();
                
                // Atualiza o status para Cancelado
                $db->prepare("UPDATE documents SET status = 'Cancelado', current_observation = ? WHERE id = ?")
                   ->execute([$obs_cancelamento, $doc_id]);
                
                // Registra o evento
                $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'Cancelamento', ?)")
                   ->execute([$doc_id, $usuario, $motivo]);
                
                $db->commit();
                header("Location: /index"); exit();
                
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro ao cancelar processo: " . $e->getMessage());
            }
        }
    }

    public function getViewerData(): array {
        $db = Database::getConnection();
        $id = (int)($_GET['id'] ?? $_POST['document_id'] ?? 0);
        
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $files = [];
        if ($doc) {
            $stmtFiles = $db->prepare("SELECT * FROM document_files WHERE document_id = ? ORDER BY file_type, id");
            $stmtFiles->execute([$id]);
            $files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [
            'doc' => $doc,
            'files' => $files,
            'role' => $_SESSION['role'] ?? ''
        ];
    }

    public function updateProcess() {
        $this->checkOperador();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return null;
        
        $db = Database::getConnection();
        $doc_id = (int)($_POST['document_id'] ?? 0);
        $name = trim($_POST['process_name'] ?? '');
        $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
        $solemp = trim($_POST['solemp'] ?? '');
        $obs = trim($_POST['observation'] ?? '');
        
        if (empty($name)) return "O nome do processo é obrigatório.";
        
        $ano_atual = date('Y');
        $upload_dir = __DIR__ . "/../../public/uploads/{$ano_atual}/reenvio_{$doc_id}";
        
        try {
            $db->beginTransaction();
            
            // Reabre o processo para o Gestor Financeiro
            $novo_status = 'Caixa de Entrada - Gestor Financeiro';
            $obs_formatada = "[" . date('d/m/Y H:i') . " - Operador]: Processo corrigido e reencaminhado. " . $obs;
            
            $db->prepare("UPDATE documents SET name = ?, cpf_cnpj = ?, solemp = ?, status = ?, current_observation = ? WHERE id = ?")
               ->execute([$name, $cpf_cnpj, $solemp, $novo_status, $obs_formatada, $doc_id]);
            
            // Processa novos ficheiros se existirem
            if (!empty($_FILES['novos_arquivos']['name'][0])) {
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $total = count($_FILES['novos_arquivos']['name']);
                for ($i = 0; $i < $total; $i++) {
                    $tmp_name = $_FILES['novos_arquivos']['tmp_name'][$i];
                    $file_name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['novos_arquivos']['name'][$i]));
                    if (move_uploaded_file($tmp_name, "{$upload_dir}/{$file_name}")) {
                        $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, ?)")
                           ->execute([$doc_id, "uploads/{$ano_atual}/reenvio_{$doc_id}/{$file_name}", 'Reenvio']);
                    }
                }
            }
            
            // Registra o evento
            $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'Correção e Reenvio', ?)")
               ->execute([$doc_id, $_SESSION['username'], $obs]);
            
            $db->commit();
            header("Location: /index"); exit();
            
        } catch (\Exception $e) {
            $db->rollBack();
            return "Erro ao atualizar: " . $e->getMessage();
        }
        
        return null;
    }
}
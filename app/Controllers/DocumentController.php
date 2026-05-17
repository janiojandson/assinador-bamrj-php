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
            
            // Não permite cancelar processos já finalizados (agora permite Arquivado)
            if (in_array($doc['status'], ['Cancelado', 'Anulado', 'Reforçado'])) {
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
                $return_url = $_POST['return_url'] ?? '/index';
                header("Location: " . $return_url); exit();
                
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro ao cancelar processo: " . $e->getMessage());
            }
        }
    }

    public function uploadNE() {
        $this->checkOperador();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $id = $_GET['id'] ?? 0;
            $status_final = $_POST['final_status'] ?? 'Arquivado';

            $stmt = $db->prepare("SELECT protocol, created_at FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch();

            if ($doc && !empty($_FILES['nota_empenho']['name'][0])) {
                $ano_doc = date('Y', strtotime($doc['created_at']));
                $upload_dir = __DIR__ . "/../../public/uploads/{$ano_doc}/{$doc['protocol']}";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $db->beginTransaction();
                try {
                    $stmt = $db->prepare("UPDATE documents SET status = ? WHERE id = ?");
                    $stmt->execute([$status_final, $id]);

                    foreach ($_FILES['nota_empenho']['name'] as $index => $originalName) {
                        if ($_FILES['nota_empenho']['error'][$index] === UPLOAD_ERR_OK) {
                            $tmp_name = $_FILES['nota_empenho']['tmp_name'][$index];
                            $name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($originalName));
                            
                            if (move_uploaded_file($tmp_name, "{$upload_dir}/{$name}")) {
                                $stmt = $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, 'Nota de Empenho')");
                                $stmt->execute([$id, "uploads/{$ano_doc}/{$doc['protocol']}/{$name}"]);
                            }
                        }
                    }

                    $stmt = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'ANEXAR_NE', ?)");
                    $stmt->execute([$id, $_SESSION['username'], "Nota de Empenho(s) ({$status_final}) anexada(s)."]);
                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollBack();
                    die("Erro ao anexar NE: " . $e->getMessage());
                }
            }
            header("Location: /index"); exit();
        }
    }

    public function processAction() {
        if (!isset($_SESSION['user_id'])) { header("Location: /login"); exit(); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $doc_id = $_GET['id'] ?? 0;
            $action = $_POST['action'] ?? ($_GET['action'] ?? '');
            
            $obs = trim($_POST['new_observation'] ?? '');
            
            if ($action === 'rejeitar' && empty($obs)) {
                header("Location: /view?id=" . $doc_id . "&aviso=falta_parecer");
                exit();
            }
            
            if ($action === 'aprovar' && empty($obs)) {
                $obs = "Processo verificado e tramitado.";
            }

            $username = $_SESSION['username'];
            $role = $_SESSION['role'];
            $is_sub = $_SESSION['is_substitute'] ?? false;

            $stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
            $stmt->execute([$doc_id]);
            $doc = $stmt->fetch();
            if (!$doc) die("Documento não encontrado.");

            $status = $doc['status'];
            $current_obs = $doc['current_observation'];

            $acao_str = ($action === 'aprovar') ? 'APROVADO' : 'REJEITADO';
            
            $cargo = $role;
            if ($role === 'Gestor_Financeiro') $cargo = 'Gestor Financeiro';
            if ($role === 'Gestor_Financeiro_Substituto') $cargo = 'Gestor Financeiro Substituto';
            if ($role === 'Chefe_Departamento') $cargo = 'Chefe de Departamento';
            if ($role === 'Agente_Fiscal') $cargo = 'Agente Fiscal';
            if ($role === 'Ordenador_Despesas') $cargo = 'Ordenador de Despesas';
            if ($is_sub) $cargo .= ' (SUBSTITUTO)';

            $timestamp = date('d/m/Y H:i');
            $current_obs .= "\n[{$timestamp} - {$cargo}]: {$acao_str} - \"{$obs}\"";

            $stmt = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, ?, ?)");
            $stmt->execute([$doc_id, $username, strtoupper($action), $obs]);

            if ($action === 'rejeitar') {
                if (in_array($role, ['Gestor_Financeiro', 'Gestor_Financeiro_Substituto'])) {
                    $status = 'Devolvido - Operador'; 
                } else {
                    $status = 'Devolvido - Gestor Financeiro'; 
                }
            } elseif ($action === 'aprovar') {
                if ($status === 'Caixa de Entrada - Gestor Financeiro' || $status === 'Devolvido - Gestor Financeiro') {
                    if ($is_sub && in_array($role, ['Gestor_Financeiro', 'Gestor_Financeiro_Substituto'])) {
                        $status = 'Caixa de Entrada - Agente Fiscal';
                    } else {
                        $status = 'Caixa de Entrada - Chefe de Departamento';
                    }
                } elseif ($status === 'Caixa de Entrada - Chefe de Departamento') {
                    $status = ($is_sub && $role === 'Chefe_Departamento') ? 'Caixa de Entrada - Ordenador de Despesas' : 'Caixa de Entrada - Agente Fiscal';
                } elseif ($status === 'Caixa de Entrada - Agente Fiscal') {
                    $status = ($is_sub && $role === 'Agente_Fiscal') ? 'Aguardando Empenho - Operador' : 'Caixa de Entrada - Ordenador de Despesas';
                } elseif ($status === 'Caixa de Entrada - Ordenador de Despesas') {
                    $status = 'Aguardando Empenho - Operador';
                }
            }

            $stmt = $db->prepare("UPDATE documents SET status = ?, current_observation = ? WHERE id = ?");
            $stmt->execute([$status, $current_obs, $doc_id]);

            header("Location: /index"); exit();
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
        
        $role = $_SESSION['role'] ?? '';
        
        if ($role === 'Usuário Comum') {
            $filteredFiles = [];
            foreach ($files as $file) {
                if ($file['file_type'] === 'Nota de Empenho') {
                    $filteredFiles[] = $file;
                }
            }
            $files = $filteredFiles;
        }
        
        return [
            'doc' => $doc,
            'files' => $files,
            'role' => $role
        ];
    }

    public function getPdf() {
        $file = $_GET['file'] ?? '';
        $file = str_replace(['../', '..\\'], '', $file); 
        $path = __DIR__ . '/../../public/' . ltrim($file, '/');

        $isDownload = isset($_GET['dl']) && $_GET['dl'] == '1';
        $disposition = $isDownload ? 'attachment' : 'inline';

        if (file_exists($path) && is_file($path)) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . $disposition . '; filename="' . basename($path) . '"');
            header('Content-Length: ' . filesize($path));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            readfile($path);
            exit();
        } else {
            http_response_code(404);
            die("<div style='padding:20px; font-family:sans-serif; background:#dc3545; color:white;'><h1>⚠️ 404 - PDF não encontrado no Servidor</h1><p>Arquivo físico ausente: " . htmlspecialchars($file) . "</p></div>");
        }
    }

    public function editProcess() {
        $this->checkOperador();
        $db = Database::getConnection();
        $id = $_GET['id'] ?? 0;

        $stmt = $db->prepare("SELECT * FROM documents WHERE id = ? AND status IN ('Devolvido - Operador', 'Arquivado', 'Cancelado', 'Anulado', 'Reforçado')");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            die("<div style='padding:20px; font-family:sans-serif; background:#dc3545; color:white;'><h1>⚠️ Acesso Negado</h1><p>Documento não encontrado ou não está disponível para correção.</p><a href='/' style='color:white;'>⬅️ Voltar ao Dashboard</a></div>");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $obs = trim($_POST['observation'] ?? '');
            
            $name = trim($_POST['process_name'] ?? $doc['name']);
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? $doc['cpf_cnpj']);
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? $doc['solemp']);

            if (empty($obs) || empty($name)) {
                die("<div style='padding:20px; font-family:sans-serif; background:#dc3545; color:white;'><h1>⚠️ Erro Tático</h1><p>É OBRIGATÓRIO informar o Assunto e o que foi corrigido no campo de despacho.</p><a href='javascript:history.back()' style='color:white;'>⬅️ Voltar</a></div>");
            }

            $protocol = $doc['protocol'];
            $ano_doc = date('Y', strtotime($doc['created_at']));
            $upload_dir = __DIR__ . "/../../public/uploads/{$ano_doc}/{$protocol}";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            try {
                $db->beginTransaction();

                $this->processarArquivos('minutas', 'Minuta', $id, $ano_doc, $protocol, $upload_dir, $db);
                $this->processarArquivos('anexos', 'Anexo', $id, $ano_doc, $protocol, $upload_dir, $db);

                $timestamp = date('d/m/Y H:i');
                $current_obs = $doc['current_observation'] . "\n[{$timestamp} - Operador]: PROCESSO EDITADO/REINICIADO - \"{$obs}\"";
                
                $novo_status = 'Caixa de Entrada - Gestor Financeiro';

                $stmt = $db->prepare("UPDATE documents SET name = ?, cpf_cnpj = ?, solemp = ?, status = ?, current_observation = ? WHERE id = ?");
                $stmt->execute([$name, $cpf_cnpj, $solemp, $novo_status, $current_obs, $id]);

                $stmt = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'EDITAR', ?)");
                $stmt->execute([$id, $_SESSION['username'], $obs]);

                $db->commit();
                header("Location: /index");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro Crítico ao Salvar: " . $e->getMessage());
            }
        }

        $stmt = $db->prepare("SELECT * FROM document_files WHERE document_id = ? ORDER BY file_type DESC, id ASC");
        $stmt->execute([$id]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/edit.php';
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
            
            $stmtDoc = $db->prepare("SELECT protocol, created_at FROM documents WHERE id = ?");
            $stmtDoc->execute([$doc_id]);
            $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);

            if ($doc) {
                $ano_doc = date('Y', strtotime($doc['created_at']));
                $protocol_dir = $doc['protocol'];
            $upload_dir = __DIR__ . "/../../public/uploads/{$ano_doc}/{$protocol_dir}";

            // Helper function to process multiple files
            $processFiles = function($fileArray, $fileType) use ($db, $doc_id, $upload_dir, $ano_doc, $protocol_dir) {
                if (!empty($fileArray['name'][0])) {
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $total = count($fileArray['name']);
                    for ($i = 0; $i < $total; $i++) {
                        if ($fileArray['error'][$i] === UPLOAD_ERR_OK) {
                            $tmp_name = $fileArray['tmp_name'][$i];
                            $file_name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($fileArray['name'][$i]));
                            if (move_uploaded_file($tmp_name, "{$upload_dir}/{$file_name}")) {
                                $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, ?)")
                                   ->execute([$doc_id, "uploads/{$ano_doc}/{$protocol_dir}/{$file_name}", $fileType]);
                            }
                        }
                    }
                }
            };

            // Processa Minutas e Anexos
            if (isset($_FILES['minutas'])) $processFiles($_FILES['minutas'], 'Minuta');
            if (isset($_FILES['anexos'])) $processFiles($_FILES['anexos'], 'Anexo');
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
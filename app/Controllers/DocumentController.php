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
            
            $protocol = $_POST['protocol'] ?? '';
            $name = $_POST['process_name'] ?? '';
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $is_priority = isset($_POST['priority']) ? 1 : 0;
            $obs = $_POST['observation'] ?? '';
            $uploader_name = $_SESSION['username'];
            $status = 'Caixa de Entrada - Enc. Finanças';

            $ano_atual = date('Y');
            
            // 1. Criação do Diretório Físico (Como no Linux original)
            $upload_dir = __DIR__ . "/../../public/uploads/{$ano_atual}/{$protocol}";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            try {
                $db->beginTransaction();

                // 2. Insere o Documento Principal
                $sql = "INSERT INTO documents (protocol, name, cpf_cnpj, solemp, status, is_priority, current_observation, uploader_name) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";
                $stmt = $db->prepare($sql);
                $obs_formatada = "[Início] " . $obs;
                $stmt->execute([$protocol, $name, $cpf_cnpj, $solemp, $status, $is_priority, $obs_formatada, $uploader_name]);
                $doc_id = $stmt->fetchColumn();

                // 3. Lógica para Múltiplos Arquivos (Minutas e Anexos)
                $this->processarArquivos('minutas', 'Minuta', $doc_id, $ano_atual, $protocol, $upload_dir, $db);
                $this->processarArquivos('anexos', 'Anexo', $doc_id, $ano_atual, $protocol, $upload_dir, $db);

                $db->commit();
                header("Location: /index");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro Crítico no Motor de Arquivos: " . $e->getMessage());
            }
        }
    }

    private function processarArquivos($inputName, $fileType, $docId, $ano, $protocol, $dir, $db) {
        if (!empty($_FILES[$inputName]['name'][0])) {
            $total = count($_FILES[$inputName]['name']);
            for ($i = 0; $i < $total; $i++) {
                $tmp_name = $_FILES[$inputName]['tmp_name'][$i];
                $name = basename($_FILES[$inputName]['name'][$i]);
                
                // Limpeza básica do nome do arquivo
                $name = preg_replace("/[^a-zA-Z0-9.-]/", "_", $name);
                
                $destination = "{$dir}/{$name}";
                $db_path = "{$ano}/{$protocol}/{$name}"; // Caminho relativo salvo no banco

                if (move_uploaded_file($tmp_name, $destination)) {
                    $stmt = $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, ?)");
                    $stmt->execute([$docId, $db_path, $fileType]);
                }
            }
        }
    }

    public function cancelProcess() {
        $this->checkOperador();
        $id = $_GET['id'] ?? 0;
        
        $db = Database::getConnection();
        $obs = 'Processo cancelado pelo operador.';
        $timestamp = date('d/m H:i');
        
        $stmt = $db->prepare("UPDATE documents SET status = 'Cancelado', current_observation = current_observation || '\n[' || ? || ' - Operador]: ' || ? WHERE id = ?");
        $stmt->execute([$timestamp, $obs, $id]);
        
        // Registra o Evento Histórico
        $stmt = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'CANCELAR', ?)");
        $stmt->execute([$id, $_SESSION['username'], $obs]);
        
        header("Location: /index");
        exit();
    }

    public function uploadNE() {
        $this->checkOperador();
        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = Database::getConnection();
            $status_final = $_POST['final_status'] ?? 'Arquivado';

            // Busca o protocolo e a data de criação para achar a pasta certa
            $stmt = $db->prepare("SELECT protocol, created_at FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch();

            if ($doc && !empty($_FILES['nota_empenho']['name'])) {
                $ano_doc = date('Y', strtotime($doc['created_at']));
                $protocol = $doc['protocol'];
                
                $upload_dir = __DIR__ . "/../../public/uploads/{$ano_doc}/{$protocol}";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $tmp_name = $_FILES['nota_empenho']['tmp_name'];
                $name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['nota_empenho']['name']));
                $destination = "{$upload_dir}/{$name}";
                $db_path = "{$ano_doc}/{$protocol}/{$name}";

                if (move_uploaded_file($tmp_name, $destination)) {
                    // Atualiza Status e Salva Arquivo
                    $db->beginTransaction();
                    
                    $stmt = $db->prepare("UPDATE documents SET status = ? WHERE id = ?");
                    $stmt->execute([$status_final, $id]);
                    
                    $stmt = $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, 'Nota de Empenho')");
                    $stmt->execute([$id, $db_path]);

                    $stmt = $db->prepare("INSERT INTO events (document_id, user_name, action, observation) VALUES (?, ?, 'ANEXAR_NE', ?)");
                    $stmt->execute([$id, $_SESSION['username'], "Nota de Empenho ({$status_final}) anexada."]);
                    
                    $db->commit();
                }
            }
            header("Location: /index");
            exit();
        }
    }
}
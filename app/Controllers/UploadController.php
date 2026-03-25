<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class UploadController {
    
    // Upload de Processo Novo (Abertura de Demanda Normal)
    public function handleUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['minutas'])) {
            $db = Database::getConnection();
            
            $dateStr = date('Ymd');
            $randomId = strtoupper(bin2hex(random_bytes(2))); 
            $protocol = "BAMRJ-{$dateStr}-{$randomId}";
            
            $process_name = $_POST['process_name'] ?? 'Sem Assunto';
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $priority = isset($_POST['priority']) ? 1 : 0;
            $observation = $_POST['observation'] ?? '';
            $username = $_SESSION['username'] ?? 'Sistema';
            $year = date('Y');

            $uploadDir = "uploads/$year/$protocol/";
            $fullPath = __DIR__ . "/../../public/" . $uploadDir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            $db->beginTransaction();
            try {
                $stmt = $db->prepare("INSERT INTO documents (protocol, name, cpf_cnpj, solemp, is_priority, current_observation, uploader_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $obs_entry = "[Início] $observation";
                
                $stmt->execute([$protocol, $process_name, $cpf_cnpj, $solemp, $priority, $obs_entry, $username, 'Caixa de Entrada - Gestor Financeiro']);
                $docId = $db->lastInsertId();

                $this->saveFiles($docId, $_FILES['minutas'], 'Minuta', $uploadDir, $fullPath);
                
                if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                    $this->saveFiles($docId, $_FILES['anexos'], 'Anexo', $uploadDir, $fullPath);
                }

                $db->commit();
                header("Location: /index");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro no upload tático: " . $e->getMessage());
            }
        }
    }

    // Upload do Acervo Histórico
    public function handleLegacyUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
            $db = Database::getConnection();
            
            $year = $_POST['ano_referencia'] ?? date('Y');
            
            // Gera Protocolo Automático (Ex: BAMRJ-LEGADO-2023-F4A1)
            $randomId = strtoupper(bin2hex(random_bytes(2)));
            $protocol = "BAMRJ-LEGADO-{$year}-{$randomId}";
            
            $process_name = $_POST['process_name'] ?? 'Acervo Legado';
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $username = $_SESSION['username'] ?? 'Sistema';
            
            // Força a data de criação para que o Arquivo consiga buscar pelo Ano Antigo
            $fakeDate = "$year-12-31 12:00:00"; 

            // Pasta Específica para Legados
            $uploadDir = "uploads/legado/$year/$protocol/";
            $fullPath = __DIR__ . "/../../public/" . $uploadDir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            $db->beginTransaction();
            try {
                // 🟢 CORREÇÃO TÁTICA: Trocado o '0' pela tipagem correta 'false' no PostgreSQL
                $stmt = $db->prepare("INSERT INTO documents (protocol, name, cpf_cnpj, solemp, is_priority, current_observation, uploader_name, status, created_at) VALUES (?, ?, ?, ?, false, ?, ?, ?, ?)");
                
                $obs_entry = "[Acervo Histórico] Documento inserido retroativamente pelo Operador $username, referencial ao ano de $year.";
                $stmt->execute([$protocol, $process_name, $cpf_cnpj, $solemp, $obs_entry, $username, 'Arquivado', $fakeDate]);
                $docId = $db->lastInsertId();

                // Salva o PDF como "Nota de Empenho" para habilitar o Download Público!
                $this->saveFiles($docId, $_FILES['documento'], 'Nota de Empenho', $uploadDir, $fullPath);

                $db->commit();
                
                // Redireciona diretamente para o Arquivo, mostrando os processos daquele ano!
                header("Location: /arquivo?ano=$year");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                die("Erro ao registrar legado: " . $e->getMessage());
            }
        }
    }

    private function saveFiles($docId, $fileArray, $type, $relPath, $fullPath) {
        $db = Database::getConnection();
        foreach ($fileArray['name'] as $key => $name) {
            if ($fileArray['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $fileArray['tmp_name'][$key];
                $safeName = basename($name);
                if (move_uploaded_file($tmpName, $fullPath . $safeName)) {
                    $stmt = $db->prepare("INSERT INTO document_files (document_id, filename, file_type) VALUES (?, ?, ?)");
                    $stmt->execute([$docId, $relPath . $safeName, $type]);
                }
            }
        }
    }
}
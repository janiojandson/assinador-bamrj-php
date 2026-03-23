<?php
namespace App\Controllers;

use App\Core\Database;
use PDO;

class UploadController {
    
    // Upload de Processo Novo (Fluxo Normal)
    public function handleUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['minutas'])) {
            $db = Database::getConnection();
            
            // Gerador de Protocolo no Backend
            $dateStr = date('Ymd');
            $randomId = strtoupper(bin2hex(random_bytes(2))); // Ex: 4F2A
            $protocol = "BAMRJ-{$dateStr}-{$randomId}";
            
            $process_name = $_POST['process_name'];
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $priority = isset($_POST['priority']) ? 1 : 0;
            $observation = $_POST['observation'] ?? '';
            $username = $_SESSION['username'] ?? 'Sistema';
            $year = date('Y');

            // Salvando na pasta PÚBLICA (Permite transparência)
            $uploadDir = "uploads/$year/$protocol/";
            $fullPath = __DIR__ . "/../../public/" . $uploadDir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            $db->beginTransaction();
            try {
                $stmt = $db->prepare("INSERT INTO documents (protocol, name, cpf_cnpj, solemp, is_priority, current_observation, uploader_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $obs_entry = "[Início] $observation";
                
                // Status Inicial devidamente atualizado para a nova nomenclatura
                $stmt->execute([$protocol, $process_name, $cpf_cnpj, $solemp, $priority, $obs_entry, $username, 'Caixa de Entrada - Gestor Financeiro']);
                $docId = $db->lastInsertId();

                $this->saveFiles($docId, $_FILES['minutas'], 'Minuta', $uploadDir, $fullPath);
                $this->saveFiles($docId, $_FILES['anexos'], 'Anexo', $uploadDir, $fullPath);

                $db->commit();
                header("Location: /index");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                return "Erro no upload tático: " . $e->getMessage();
            }
        }
        return null;
    }

    // Upload de Empenhos Antigos (Acervo Histórico / Legado)
    public function handleLegacyUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
            $db = Database::getConnection();
            
            $protocol = $_POST['protocol_legacy'];
            if (empty($protocol)) {
                $protocol = "BAMRJ-LEGADO-" . strtoupper(bin2hex(random_bytes(3)));
            }
            
            $process_name = $_POST['process_name'];
            $cpf_cnpj = preg_replace('/\D/', '', $_POST['cpf_cnpj'] ?? '');
            $solemp = preg_replace('/\D/', '', $_POST['solemp'] ?? '');
            $year = $_POST['ano_referencia'];
            $username = $_SESSION['username'] ?? 'Sistema';
            
            // Força a data para aparecer corretamente na busca por ano do Arquivo
            $fakeDate = "$year-12-31 12:00:00"; 

            // Salvando na pasta PÚBLICA (Permite transparência)
            $uploadDir = "uploads/legado/$year/$protocol/";
            $fullPath = __DIR__ . "/../../public/" . $uploadDir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            $db->beginTransaction();
            try {
                $stmt = $db->prepare("INSERT INTO documents (protocol, name, cpf_cnpj, solemp, current_observation, uploader_name, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $obs_entry = "[Acervo Histórico] Documento inserido retroativamente pelo Operador $username.";
                $stmt->execute([$protocol, $process_name, $cpf_cnpj, $solemp, $obs_entry, $username, 'Arquivado', $fakeDate]);
                $docId = $db->lastInsertId();

                $this->saveFiles($docId, $_FILES['documento'], 'Empenho Legado', $uploadDir, $fullPath);

                $db->commit();
                header("Location: /arquivo?ano=$year");
                exit();
            } catch (\Exception $e) {
                $db->rollBack();
                return "Erro ao registrar legado: " . $e->getMessage();
            }
        }
        return null;
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
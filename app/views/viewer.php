<?php
$docCtrl = new \App\Controllers\DocumentController();
$db = \App\Core\Database::getConnection();
$docId = $_GET['id'] ?? 0;
$error = null;

// Processamento da decisão do oficial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $error = $docCtrl->processAction($docId, $_POST['action'], $_POST['observation']);
}

$stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch();

$stmtFiles = $db->prepare("SELECT * FROM document_files WHERE document_id = ?");
$stmtFiles->execute([$docId]);
$files = $stmtFiles->fetchAll();

if (!$doc) die("Processo não encontrado.");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizador - <?php echo htmlspecialchars($doc['protocol']); ?></title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        body { display: flex; height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; overflow: hidden; }
        #sidebar { width: 350px; background: #2c3e50; color: white; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; }
        #content { flex-grow: 1; background: #95a5a6; position: relative; }
        iframe { width: 100%; height: 100%; border: none; }
        .file-item { padding: 12px; border-bottom: 1px solid #34495e; cursor: pointer; transition: 0.2s; }
        .file-item:hover { background: #34495e; }
        .action-panel { margin-top: 20px; padding-top: 20px; border-top: 2px solid #34495e; }
        textarea { width: 100%; padding: 10px; border-radius: 4px; border: none; margin-bottom: 10px; resize: vertical; }
        .history-box { margin-top: 20px; font-size: 0.85em; background: #1a252f; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div id="sidebar">
        <h3><?php echo htmlspecialchars($doc['protocol']); ?></h3>
        <p><strong>Status Atual:</strong><br><?php echo htmlspecialchars($doc['status']); ?></p>
        
        <hr style="width:100%; border: 0; border-top: 1px solid #34495e;">
        
        <h4>Documentos do Processo</h4>
        <?php foreach ($files as $f): ?>
            <div class="file-item" onclick="loadPDF('/<?php echo $f['filename']; ?>')">
                📄 <?php echo basename($f['filename']); ?> <br>
                <small style="color: #bdc3c7;"><?php echo $f['file_type']; ?></small>
            </div>
        <?php endforeach; ?>

        <div class="action-panel">
            <h4>Despacho e Decisão</h4>
            <form method="POST">
                <textarea name="observation" rows="4" placeholder="Digite seu despacho aqui..." required></textarea>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="action" value="aprovar" style="flex:1; background:#27ae60; color:white; border:none; padding:12px; border-radius:4px; cursor:pointer; font-weight:bold;">APROVAR</button>
                    <button type="submit" name="action" value="rejeitar" style="flex:1; background:#e74c3c; color:white; border:none; padding:12px; border-radius:4px; cursor:pointer; font-weight:bold;">REJEITAR</button>
                </div>
            </form>
            <?php if ($error): ?>
                <p style="color:#ff7675; margin-top:10px;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>

        <div class="history-box">
            <strong>Histórico de Tramitação:</strong>
            <div style="white-space: pre-wrap; margin-top: 5px; color: #bdc3c7; font-family: monospace;">
                <?php echo htmlspecialchars($doc['current_observation']); ?>
            </div>
        </div>

        <br>
        <a href="/index" style="color: #bdc3c7; text-decoration: none; font-size: 0.9em;">← Voltar ao Dashboard</a>
    </div>

    <div id="content">
        <iframe id="pdf-frame" src=""></iframe>
    </div>

    <script>
        function loadPDF(path) {
            document.getElementById('pdf-frame').src = path;
        }
        // Carrega o primeiro arquivo automaticamente se existir
        <?php if (!empty($files)): ?>
            window.onload = () => loadPDF('/<?php echo $files[0]['filename']; ?>');
        <?php endif; ?>
    </script>
</body>
</html>
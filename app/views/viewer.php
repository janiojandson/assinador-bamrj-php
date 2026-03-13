<?php
$docCtrl = new \App\Controllers\DocumentController();
$dados = $docCtrl->getViewerData();

$doc = $dados['doc'];
$files = $dados['files'];
$role = $dados['role'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizador - <?= htmlspecialchars($doc['protocol']) ?></title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        .viewer-container { display: flex; height: 100vh; overflow: hidden; font-family: Arial, sans-serif; }
        .sidebar { width: 350px; background: #f8f9fa; padding: 20px; overflow-y: auto; border-right: 1px solid #ddd; }
        .pdf-area { flex: 1; background: #525659; display: flex; flex-direction: column; }
        .file-btn { display: block; width: 100%; text-align: left; padding: 10px; margin-bottom: 5px; background: white; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; transition: 0.2s; }
        .file-btn:hover { background: #e9ecef; }
        .file-btn.active { background: #004488; color: white; border-color: #002244; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body style="margin:0;">

<div class="viewer-container">
    <div class="sidebar">
        <a href="<?= $role === 'Usuário Comum' ? '/arquivo' : '/' ?>" style="display:inline-block; margin-bottom: 15px; color: #004488; text-decoration: none; font-weight: bold; background: #e2e3e5; padding: 5px 10px; border-radius: 4px;">⬅️ Voltar</a>
        
        <h3 style="margin-top:0; color: #002244;">Protocolo: <?= htmlspecialchars($doc['protocol']) ?></h3>
        <p><b>Assunto:</b> <?= htmlspecialchars($doc['name']) ?></p>
        <p><b>Status:</b> <span style="background: #ffcc00; padding: 2px 5px; border-radius: 3px; font-weight:bold;"><?= htmlspecialchars($doc['status']) ?></span></p>
        
        <hr style="border-top: 1px solid #ddd; margin: 15px 0;">

        <?php if (in_array($role, ['Enc_Financas', 'Ajudante_Encarregado', 'Chefe_Departamento', 'Vice_Diretor', 'Diretor']) && strpos($doc['status'], 'Caixa de Entrada') !== false): ?>
            <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #004488;">
                <form action="/process_action?id=<?= $doc['id'] ?>&action=aprovar" method="POST" style="margin-bottom: 10px;">
                    <textarea name="new_observation" placeholder="Parecer favorável (opcional)..." style="width: 100%; height: 60px; margin-bottom: 10px; padding: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 3px;"></textarea>
                    <button type="submit" style="width: 100%; background: #28a745; color: white; border: none; padding: 10px; font-weight: bold; cursor: pointer; border-radius: 4px;">✅ APROVAR E TRAMITAR</button>
                </form>
                <form action="/process_action?id=<?= $doc['id'] ?>&action=rejeitar" method="POST" onsubmit="return confirm('ATENÇÃO: Este processo será devolvido ao Operador. Confirma a rejeição?');">
                    <textarea name="new_observation" placeholder="Motivo da devolução (obrigatório)" required style="width: 100%; height: 60px; margin-bottom: 10px; padding: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 3px;"></textarea>
                    <button type="submit" style="width: 100%; background: #dc3545; color: white; border: none; padding: 10px; font-weight: bold; cursor: pointer; border-radius: 4px;">❌ DEVOLVER AO OPERADOR</button>
                </form>
            </div>
            <hr style="border-top: 1px solid #ddd; margin: 15px 0;">
        <?php endif; ?>

        <h4 style="color: #002244;">📄 Documentos do Processo</h4>
        <div id="file-list">
            <?php foreach ($files as $index => $f): ?>
                <button class="file-btn <?= $index === 0 ? 'active' : '' ?>" onclick="loadPdf('<?= htmlspecialchars($f['filename']) ?>', this)">
                    <b><?= htmlspecialchars($f['file_type']) ?></b><br>
                    <small style="word-break: break-all; color: #555;"><?= htmlspecialchars(basename($f['filename'])) ?></small>
                </button>
            <?php endforeach; ?>
        </div>

        <hr style="border-top: 1px solid #ddd; margin: 15px 0;">

        <h4 style="color: #002244;">📜 Histórico e Despachos</h4>
        <div style="font-size: 0.85em; background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 4px; max-height: 250px; overflow-y: auto; white-space: pre-wrap; font-family: monospace; line-height: 1.4;">
            <?= htmlspecialchars($doc['current_observation']) ?>
        </div>
    </div>

    <div class="pdf-area">
        <?php if (count($files) > 0): ?>
            <iframe id="pdf-frame" src="/get_pdf?file=<?= urlencode($files[0]['filename']) ?>#toolbar=1&navpanes=0&scrollbar=0"></iframe>
        <?php else: ?>
            <div style="color: white; padding: 20px; text-align: center; font-size: 1.2em; margin-top: 50px;">
                ⚠️ Nenhum documento PDF foi anexado a este processo.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function loadPdf(filename, btn) {
        document.getElementById('pdf-frame').src = '/get_pdf?file=' + encodeURIComponent(filename) + '#toolbar=1&navpanes=0&scrollbar=0';
        document.querySelectorAll('.file-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
</script>
</body>
</html>
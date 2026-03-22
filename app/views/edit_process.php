<?php
$page_title = 'Corrigir Processo Devolvido';
require __DIR__ . '/partials/header.php';

$docCtrl = new \App\Controllers\DocumentController();

// Se for POST, tenta atualizar
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = $docCtrl->updateProcess();
}

// Se for GET, busca os dados para preencher o formulário
$dados = $docCtrl->getViewerData();
$doc = $dados['doc'];
$files = $dados['files'];

if (!$doc || !in_array($doc['status'], ['Devolvido - Operador', 'Arquivado', 'Cancelado', 'Anulado', 'Reforçado'])) {
    die("<div style='padding:20px; text-align:center;'><h2>Acesso Negado</h2><p>Este processo não pode ser editado neste momento.</p><a href='/index'>Voltar</a></div>");
}
?>

<div class="container" style="max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #ffcc00;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #002244;">✏️ Corrigir / Reiniciar Processo</h2>
        <a href="/index" style="color: #666; text-decoration: none; font-weight: bold; background: #e9ecef; padding: 8px 15px; border-radius: 4px;">⬅️ Cancelar e Voltar</a>
    </div>

    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 25px; border-left: 4px solid #ffeeba;">
        <strong>Atenção Operador:</strong> Ao salvar, este processo sairá do status <em>"<?= htmlspecialchars($doc['status']) ?>"</em> e será reencaminhado para a <strong>Caixa de Entrada do Enc. de Finanças</strong>.
    </div>

    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #002244;">Protocolo (Intocável):</label>
            <input type="text" value="<?= htmlspecialchars($doc['protocol']) ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background: #e9ecef; color: #d32f2f; font-weight: bold; font-family: monospace;">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #002244;">Assunto do Processo:</label>
            <input type="text" name="process_name" value="<?= htmlspecialchars($doc['name']) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: bold; color: #002244;">CPF / CNPJ:</label>
                <input type="text" name="cpf_cnpj" value="<?= htmlspecialchars($doc['cpf_cnpj']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div class="form-group" style="flex: 1;">
                <label style="font-weight: bold; color: #002244;">SOLEMP / NUP:</label>
                <input type="text" name="solemp" value="<?= htmlspecialchars($doc['solemp']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: inline-block; background: <?= $doc['is_priority'] ? '#ffeeba' : '#f8f9fa' ?>; padding: 10px 15px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
                <input type="checkbox" name="priority" value="1" <?= $doc['is_priority'] ? 'checked' : '' ?>> 
                <span style="color: #d32f2f; font-weight: bold;">🚩 Manter/Marcar como Prioritário</span>
            </label>
        </div>

        <div style="background: #f1f3f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px dashed #6c757d;">
            <h4 style="margin-top: 0; color: #333;">Ficheiros já anexados ao processo:</h4>
            <ul style="margin-bottom: 0; color: #555; font-size: 0.9em;">
                <?php foreach ($files as $f): ?>
                    <li>[<?= $f['file_type'] ?>] - <?= htmlspecialchars(basename($f['filename'])) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
            <div style="flex: 1; background: #e2e3e5; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8;">
                <label><b>Anexar novas Minutas/Correções (PDF):</b></label><br>
                <small style="color: #666;">Opcional. Junta-se aos ficheiros antigos.</small>
                <input type="file" name="minutas[]" accept=".pdf" multiple style="margin-top:10px; width: 100%;">
            </div>
            <div style="flex: 1; background: #e2e3e5; padding: 15px; border-radius: 5px; border-left: 4px solid #6c757d;">
                <label><b>Anexar novos Anexos (PDF):</b></label><br>
                <small style="color: #666;">Opcional. Certidões, propostas, etc.</small>
                <input type="file" name="anexos[]" accept=".pdf" multiple style="margin-top:10px; width: 100%;">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: bold; color: #002244;">Nota de Correção / Despacho de Reenvio:</label>
            <textarea name="observation" rows="3" required placeholder="Explique sucintamente o que foi corrigido..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;"></textarea>
        </div>

        <button type="submit" style="width: 100%; background: #004488; color: white; padding: 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.2); transition: 0.2s;">
            ♻️ GRAVAR E REINICIAR TRAMITAÇÃO TÁTICA
        </button>
    </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
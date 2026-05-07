<?php
$page_title = 'BAMRJ | Inserir Novo Documento';
require __DIR__ . '/partials/header.php';

$uploadCtrl = new \App\Controllers\UploadController();
$error = $uploadCtrl->handleUpload();

$dateStr = date('Ymd');
$randomId = strtoupper(substr(uniqid(), -4));
$suggestedProtocol = "BAMRJ-{$dateStr}-{$randomId}";
?>

<style>
    .file-list-item { background: #ecf0f1; padding: 8px 12px; margin-top: 5px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; font-size: 0.9em; border: 1px solid #bdc3c7;}
    .remove-file { color: #e74c3c; cursor: pointer; font-weight: bold; padding: 0 5px; }
    .remove-file:hover { color: #c0392b; }
</style>

<div style="max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-top: 4px solid #00447c;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #002244; margin: 0;">📄 Inserir Novo Documento</h2>
        <a href="/" class="btn-nav-inicio">🏠 Tela Inicial</a>
    </div>

    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Protocolo (Gerado Automaticamente):</label>
            <input type="text" name="protocol" value="<?php echo $suggestedProtocol; ?>" readonly style="width: 100%; padding: 10px; background: #f4f4f4; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">NE:</label>
            <input type="text" name="process_name" required placeholder="Ex: 2024xxxx" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">CPF / CNPJ:</label>
                <input type="text" name="cpf_cnpj" placeholder="Apenas números" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">SOLEMP:</label>
                <input type="text" name="solemp" placeholder="Número da SOLEMP" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Observação Inicial:</label>
            <textarea name="observation" rows="3" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; box-sizing: border-box;"></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Arquivos (PDF, Imagens, etc.):</label>
            <input type="file" name="files[]" id="fileInput" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <div id="fileList" style="margin-top: 10px;"></div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="/" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Cancelar</a>
            <button type="submit" style="padding: 10px 20px; background: #00447c; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">📤 Enviar Documento</button>
        </div>
    </form>
</div>

<script>
document.getElementById('fileInput').addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    for (let i = 0; i < this.files.length; i++) {
        const div = document.createElement('div');
        div.className = 'file-list-item';
        div.innerHTML = '<span>📎 ' + this.files[i].name + ' (' + (this.files[i].size / 1024).toFixed(1) + ' KB)</span><span class="remove-file" onclick="this.parentElement.remove(); document.getElementById(\'fileInput\').value=\'\';">✕</span>';
        fileList.appendChild(div);
    }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
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
    .upload-section { background: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6; margin-bottom: 15px; }
    .upload-section h4 { margin: 0 0 10px 0; color: #002244; }
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

        <label style="display: inline-block; margin-bottom: 20px; background: #fff3cd; padding: 10px 15px; border-radius: 4px; border: 1px solid #ffeeba; cursor: pointer; font-weight: bold;">
            <input type="checkbox" name="priority" value="1"> 🚩 Marcar como Processo Prioritário (URGENTE)
        </label>

        <!-- 🐛 FIX Bug #2: Dois campos de upload separados com suporte a múltiplos ficheiros -->
        <div class="upload-section">
            <h4>📝 Minutas (Documentos Principais)</h4>
            <input type="file" name="minutas[]" id="minutasInput" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <div id="minutasList" style="margin-top: 10px;"></div>
        </div>

        <div class="upload-section">
            <h4>📎 Outros (Anexos Complementares)</h4>
            <input type="file" name="anexos[]" id="anexosInput" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <div id="anexosList" style="margin-top: 10px;"></div>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="/" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Cancelar</a>
            <button type="submit" style="padding: 10px 20px; background: #00447c; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">📤 Enviar Documento</button>
        </div>
    </form>
</div>

<script>
// 🐛 FIX: Preview de ficheiros para ambos os campos com exclusão e adição sem sobrescrever
function setupFilePreview(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    let dt = new DataTransfer();

    input.addEventListener('change', function(e) {
        for (let i = 0; i < this.files.length; i++) {
            dt.items.add(this.files[i]);
        }
        input.files = dt.files;
        renderList();
    });

    function renderList() {
        list.innerHTML = '';
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const div = document.createElement('div');
            div.className = 'file-list-item';
            div.innerHTML = '<span>📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)</span><span class="remove-file" data-index="' + i + '">✕</span>';
            list.appendChild(div);
        }

        const removeBtns = list.querySelectorAll('.remove-file');
        removeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeFile(index);
            });
        });
    }

    function removeFile(index) {
        const dtNew = new DataTransfer();
        for (let i = 0; i < input.files.length; i++) {
            if (i !== index) {
                dtNew.items.add(input.files[i]);
            }
        }
        dt = dtNew;
        input.files = dt.files;
        renderList();
    }
}

setupFilePreview('minutasInput', 'minutasList');
setupFilePreview('anexosInput', 'anexosList');
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
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
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nome do Processo / Favorecido:</label>
            <input type="text" name="process_name" required placeholder="Ex: Aquisição de Material de Escritório" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
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

        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px dashed #00447c;">
            <label style="font-weight: bold; color: #00447c; display: block; margin-bottom: 10px;">📄 Minutas (PDF):</label>
            <input type="file" name="minutas[]" multiple accept=".pdf" required style="margin-bottom: 15px; width: 100%;">
            
            <hr style="border-top: 1px solid #ddd; margin: 15px 0;">

            <label style="font-weight: bold; color: #555; display: block; margin-bottom: 10px;">📄 Empenhos Assinados (Opcional nesta fase):</label>
            <input type="file" name="anexos[]" multiple accept=".pdf" id="empenho-input" style="width: 100%;">
            <div id="empenho-file-list" style="margin-top: 10px;"></div>
        </div>

        <div style="margin-top: 15px; background: #fff5f5; padding: 15px; border-left: 4px solid #e74c3c; border-radius: 4px;">
            <input type="checkbox" name="priority" id="priority">
            <label for="priority" style="color: #e74c3c; font-weight: bold; cursor: pointer;"> 🚩 Marcar como Prioridade Alta</label>
        </div>

        <button type="submit" style="width: 100%; margin-top: 20px; height: 50px; background: #00447c; color: white; font-weight: bold; font-size: 1.1em; border: none; border-radius: 4px; cursor: pointer;">
            LANÇAR PROCESSO NA REDE
        </button>
    </form>
</div>

<script>
    // Motor JS Vanilla para gestão de múltiplos ficheiros (Empenhos)
    const empenhoInput = document.getElementById('empenho-input');
    const fileListContainer = document.getElementById('empenho-file-list');
    let dataTransfer = new DataTransfer();

    empenhoInput.addEventListener('change', function() {
        for(let file of this.files) {
            dataTransfer.items.add(file);
        }
        this.files = dataTransfer.files;
        renderFileList();
    });

    function renderFileList() {
        fileListContainer.innerHTML = '';
        Array.from(dataTransfer.files).forEach((file, index) => {
            let div = document.createElement('div');
            div.className = 'file-list-item';
            div.innerHTML = `<span>📄 ${file.name}</span> <span class="remove-file" onclick="removeFile(${index})" title="Remover Ficheiro">✖</span>`;
            fileListContainer.appendChild(div);
        });
    }

    function removeFile(indexToRemove) {
        let newDataTransfer = new DataTransfer();
        Array.from(dataTransfer.files).forEach((file, index) => {
            if (index !== indexToRemove) {
                newDataTransfer.items.add(file);
            }
        });
        dataTransfer = newDataTransfer;
        empenhoInput.files = dataTransfer.files;
        renderFileList();
    }
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
<?php
$uploadCtrl = new \App\Controllers\UploadController();
$error = $uploadCtrl->handleUpload();

$dateStr = date('Ymd');
$randomId = strtoupper(substr(uniqid(), -4));
$suggestedProtocol = "BAMRJ-{$dateStr}-{$randomId}";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Processo - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        .file-list-item { background: #ecf0f1; padding: 8px 12px; margin-top: 5px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; font-size: 0.9em; border: 1px solid #bdc3c7;}
        .remove-file { color: #e74c3c; cursor: pointer; font-weight: bold; padding: 0 5px; }
        .remove-file:hover { color: #c0392b; }
    </style>
</head>
<body>
    <header style="background: #00447c; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 40px;">
            <strong>BAMRJ | Inserir Novo Documento</strong>
        </div>
        <a href="/index" style="color: white; text-decoration: none; border: 1px solid white; padding: 6px 15px; border-radius: 4px; font-weight: bold; background: rgba(255,255,255,0.1);">🏠 INÍCIO / DASHBOARD</a>
    </header>

    <div class="container" style="max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Protocolo (Gerado Automaticamente):</label>
                <input type="text" name="protocol" value="<?php echo $suggestedProtocol; ?>" readonly style="background: #f4f4f4; font-weight: bold;">
            </div>

            <div class="form-group">
                <label>Nome do Processo / Favorecido:</label>
                <input type="text" name="process_name" required placeholder="Ex: Aquisição de Material de Escritório">
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>CPF / CNPJ:</label>
                    <input type="text" name="cpf_cnpj" placeholder="Apenas números">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>SOLEMP:</label>
                    <input type="text" name="solemp" placeholder="Número da SOLEMP">
                </div>
            </div>

            <div class="form-group">
                <label>Observação Inicial:</label>
                <textarea name="observation" rows="3" style="width: 100%; border-radius: 4px; border: 1px solid #ccc;"></textarea>
            </div>

            <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px dashed #00447c;">
                
                <label><strong>Minutas (PDF):</strong></label>
                <input type="file" name="minutas[]" multiple accept=".pdf" required style="margin-bottom: 15px;">
                
                <hr style="border-top: 1px solid #ddd; margin: 15px 0;">

                <label><strong>Empenhos Assinados (PDF):</strong> <small style="color: #7f8c8d;">(Opcional nesta fase)</small></label>
                <input type="file" name="anexos[]" multiple accept=".pdf" id="empenho-input">
                <div id="empenho-file-list" style="margin-top: 10px;"></div>
                
            </div>

            <div class="form-group" style="margin-top: 15px; background: #fff5f5; padding: 10px; border-left: 4px solid #e74c3c;">
                <input type="checkbox" name="priority" id="priority">
                <label for="priority" style="color: #e74c3c; font-weight: bold;"> Marcar como Prioridade Alta</label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px; height: 50px; font-size: 1.1em;">
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
</body>
</html>
<?php
$uploadCtrl = new \App\Controllers\UploadController();
$error = $uploadCtrl->handleLegacyUpload();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Acervo Histórico - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <header style="background: #2c3e50;">
        <div><strong>BAMRJ</strong> | Inserir Empenho Legado (Arquivo)</div>
        <a href="/index" style="color: white; text-decoration: none;">Voltar</a>
    </header>

    <div class="container" style="max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-top: 5px solid #f39c12;">
        
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #ffeeba;">
            <strong>Atenção:</strong> Os documentos inseridos aqui não passarão pela cadeia de aprovação. Eles irão <strong>diretamente para o Arquivo Geral</strong> com status de "Arquivado". Use isso apenas para processos físicos antigos ou finalizados fora do sistema.
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 2;">
                    <label>Protocolo Antigo (Opcional):</label>
                    <input type="text" name="protocol_legacy" placeholder="Deixe em branco para gerar um automático">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Ano de Referência do Empenho:</label>
                    <select name="ano_referencia" required>
                        <?php for($i = date('Y'); $i >= 2010; $i--): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nome do Favorecido / Assunto:</label>
                <input type="text" name="process_name" required>
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>CPF / CNPJ:</label>
                    <input type="text" name="cpf_cnpj">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Nº SOLEMP ou NUP:</label>
                    <input type="text" name="solemp">
                </div>
            </div>

            <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px dashed #f39c12;">
                <label><strong>Documento Final (PDF da Nota de Empenho assinada):</strong></label>
                <input type="file" name="documento[]" accept=".pdf" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px; background-color: #f39c12; height: 50px;">
                ARQUIVAR DOCUMENTO HISTÓRICO
            </button>
        </form>
    </div>
</body>
</html>
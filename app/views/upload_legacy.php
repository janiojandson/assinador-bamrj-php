<?php
$page_title = 'BAMRJ | Inserir Empenho Legado (Arquivo Histórico)';
require __DIR__ . '/partials/header.php';

// $uploadCtrl = new \App\Controllers\UploadController();
// $error = $uploadCtrl->handleUploadLegacy();
?>

<style>
    .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
    .form-control:focus { border-color: #00447c; outline: none; box-shadow: 0 0 5px rgba(0,68,124,0.3); }
</style>

<div style="max-width: 850px; margin: 30px auto; background: white; padding: 35px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #e67e22;">
    
    <div style="background: #fff3cd; color: #856404; padding: 18px; border-radius: 4px; margin-bottom: 25px; border-left: 5px solid #ffc107; font-size: 0.95em;">
        <strong style="font-size: 1.1em;">Atenção:</strong> Os documentos inseridos aqui não passarão pela cadeia de aprovação. Eles irão <strong>diretamente para o Arquivo Geral</strong> com o status de "Arquivado". Use esta ferramenta exclusivamente para digitalizar processos físicos antigos ou finalizados.
    </div>

    <form method="POST" enctype="multipart/form-data">
        
        <div style="display: flex; gap: 20px; margin-bottom: 18px;">
            <div class="form-group" style="flex: 2; margin: 0;">
                <label style="font-weight: bold; color: #444; display: block; margin-bottom: 5px;">Protocolo Antigo (Opcional):</label>
                <input type="text" name="protocolo_antigo" class="form-control" placeholder="Deixe em branco para auto-gerar">
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label style="font-weight: bold; color: #444; display: block; margin-bottom: 5px;">Ano de Referência:</label>
                <select name="ano_referencia" class="form-control" required>
                    <?php for($i = date('Y'); $i >= 2010; $i--): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 18px;">
            <label style="font-weight: bold; color: #444; display: block; margin-bottom: 5px;">Nome do Favorecido / Assunto:</label>
            <input type="text" name="process_name" class="form-control" required placeholder="Ex: Aquisição de Material de Limpeza">
        </div>

        <div style="display: flex; gap: 20px; margin-bottom: 25px;">
            <div class="form-group" style="flex: 1; margin: 0;">
                <label style="font-weight: bold; color: #444; display: block; margin-bottom: 5px;">CPF / CNPJ:</label>
                <input type="text" name="cpf_cnpj" class="form-control" placeholder="Apenas números">
            </div>
            <div class="form-group" style="flex: 1; margin: 0;">
                <label style="font-weight: bold; color: #444; display: block; margin-bottom: 5px;">Nº SOLEMP ou NUP:</label>
                <input type="text" name="solemp" class="form-control" placeholder="Número da SOLEMP">
            </div>
        </div>

        <div class="form-group" style="background: #fdfaf6; padding: 20px; border-radius: 6px; border: 2px dashed #e67e22; margin-bottom: 30px; text-align: center;">
            <label style="font-weight: bold; color: #d35400; display: block; margin-bottom: 12px; font-size: 1.1em;">📄 Documento Final (PDF da Nota de Empenho assinada):</label>
            <input type="file" name="documento_final" accept=".pdf" required style="width: 100%; max-width: 400px; padding: 10px; background: white; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;">
        </div>

        <button type="submit" id="btn-submit-legacy" style="width: 100%; height: 55px; background: #e67e22; color: white; font-weight: bold; font-size: 1.1em; border: none; border-radius: 4px; cursor: pointer; text-transform: uppercase;">
            Arquivar Documento Histórico
        </button>
    </form>
</div>

<script>
    const btnSubmit = document.getElementById('btn-submit-legacy');
    btnSubmit.addEventListener('mouseover', () => btnSubmit.style.background = '#d35400');
    btnSubmit.addEventListener('mouseout', () => btnSubmit.style.background = '#e67e22');
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
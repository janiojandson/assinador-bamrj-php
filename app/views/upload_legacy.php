<?php
$page_title = 'Inserir Acervo Histórico';
require __DIR__ . '/partials/header.php';

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['Operador', 'Admin'])) {
    header("Location: /index");
    exit;
}
?>
<div style="max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #f39c12;">
    <h2 style="color: #002244; margin-top: 0;">📄 Inserir Acervo Histórico (Legado)</h2>
    <p style="color: #666; margin-bottom: 25px;">Utilize esta ferramenta padronizada para digitalizar processos antigos. Eles irão diretamente para o Arquivo Geral e Consulta Pública com o status "Arquivado".</p>

    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <strong style="font-size: 1.1em;">✅ Sucesso!</strong><br>
            O documento <b><?= htmlspecialchars($_GET['protocol'] ?? '') ?></b> foi arquivado corretamente. Pode inserir o próximo documento abaixo.
        </div>
    <?php endif; ?>

    <form action="/upload_legado" method="POST" enctype="multipart/form-data">
        
        <div style="background: #e9ecef; padding: 12px; border-radius: 5px; border-left: 4px solid #6c757d; margin-bottom: 20px;">
            <strong>Próximo Protocolo:</strong> <code style="font-size: 1.1em; color: #d32f2f;">BAMRJ-LEGADO-[ANO]-Automático</code>
        </div>
        
        <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 300px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Assunto do Processo *</label>
                <input type="text" name="process_name" placeholder="Ex: Aquisição de Material..." required autofocus style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Ano de Referência *</label>
                <select name="ano_referencia" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
                    <?php for($i = date('Y'); $i >= 2010; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">CPF ou CNPJ (Opcional)</label>
                <input type="text" name="cpf_cnpj" placeholder="Apenas números" style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nº da SOLEMP/NE (Opcional)</label>
                <input type="text" name="solemp" placeholder="Pode deixar vazio" style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            </div>
        </div>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px dashed #f39c12; margin-bottom: 25px;">
            <label style="font-weight: bold; display: block; margin-bottom: 10px; color: #d35400;">📄 Documento Final / Nota de Empenho (PDF) *</label>
            <input type="file" name="documento[]" required accept="application/pdf" style="width: 100%;">
            <small style="color: #666; display: block; margin-top: 5px;">Este arquivo ficará disponível para download imediato na Consulta Pública.</small>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background: #f39c12; color: white; padding: 12px 25px; border: none; cursor: pointer; font-weight: bold; flex: 2; border-radius: 4px; font-size: 1.05em;">🗄️ Arquivar Documento Histórico</button>
            <a href="/index" style="background: #6c757d; color: white; text-align: center; text-decoration: none; padding: 12px 20px; flex: 1; border-radius: 4px; font-weight: bold; font-size: 1.05em; display: inline-block; box-sizing: border-box;">Cancelar e Voltar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
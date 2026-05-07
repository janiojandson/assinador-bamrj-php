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
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="color: #002244; margin: 0;">📄 Inserir Acervo Histórico (Legado)</h2>
        <a href="/" class="btn-nav-inicio">🏠 Tela Inicial</a>
    </div>

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
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Número do Documento Fiscal *</label>
                <input type="text" name="num_doc_fiscal" placeholder="Ex: NF 1234" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Observação Inicial</label>
            <textarea name="observation" rows="3" placeholder="Informações adicionais sobre o processo..." style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;"></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="font-weight: bold; display: block; margin-bottom: 5px;">Arquivo Digitalizado (PDF, Imagem) *</label>
            <input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png,.gif" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="/" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Cancelar</a>
            <button type="submit" style="padding: 10px 20px; background: #f39c12; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">📦 Arquivar Documento</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
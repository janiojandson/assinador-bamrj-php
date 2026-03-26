<?php
$page_title = 'Visualizador de Processo';
$hide_navbar = true; 
require __DIR__ . '/partials/header.php';

$docCtrl = new \App\Controllers\DocumentController();
$dados = $docCtrl->getViewerData();

$doc = $dados['doc'];
$files = $dados['files'];
$role = $dados['role'];
?>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<style>
    .container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    .viewer-container { display: flex; height: 100vh; overflow: hidden; background: #e9ecef; }
    
    .pdf-area-left { flex: 1; background: #525659; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; }
    .sidebar-right { width: 500px; background: #f8f9fa; padding: 25px; overflow-y: auto; border-left: 3px solid #ccc; display: flex; flex-direction: column; box-shadow: -4px 0 10px rgba(0,0,0,0.05); }
    
    .btn-group { display: flex; gap: 10px; margin-top: 15px; }
    .btn-group button { flex: 1; padding: 12px; font-size: 1.05em; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; color: white; transition: 0.2s; }
    .btn-aprovar { background-color: #28a745; }
    .btn-aprovar:hover { background-color: #218838; }
    .btn-devolver { background-color: #dc3545; }
    .btn-devolver:hover { background-color: #c82333; }
    
    .info-box { background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px; }
</style>

<div class="viewer-container">
    <div class="pdf-area-left">
        <?php if (count($files) > 0): ?>
            <h3 id="loading-msg" style="color: white; text-align: center; margin-top: 20px;">⚙️ A unificar e decodificar documentos... Aguarde.</h3>
            <iframe id="single-pdf-viewer" style="width: 100%; height: 95vh; max-width: 1200px; border: none; display: none; background: white; border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.4);" src=""></iframe>
            <script>
                document.addEventListener("DOMContentLoaded", async () => {
                    const pdfUrls = [
                        <?php foreach ($files as $f): ?>
                            "/<?= ltrim($f['filename'], '/') ?>",
                        <?php endforeach; ?>
                    ];
                    try {
                        const { PDFDocument } = PDFLib;
                        const mergedPdf = await PDFDocument.create();
                        for (let url of pdfUrls) {
                            const pdfBytes = await fetch(url).then(res => res.arrayBuffer());
                            const pdf = await PDFDocument.load(pdfBytes);
                            const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                            copiedPages.forEach((page) => mergedPdf.addPage(page));
                        }
                        const mergedPdfFile = await mergedPdf.save();
                        const blob = new Blob([mergedPdfFile], { type: 'application/pdf' });
                        const blobUrl = URL.createObjectURL(blob);
                        const viewer = document.getElementById('single-pdf-viewer');
                        viewer.src = blobUrl + "#toolbar=1&navpanes=0";
                        viewer.style.display = 'block';
                        document.getElementById('loading-msg').style.display = 'none';
                    } catch (err) {
                        console.error("Erro na Fusão de PDFs: ", err);
                        document.getElementById('loading-msg').innerText = "⚠️ Ocorreu um erro ao unificar os documentos. Tente recarregar a página.";
                    }
                });
            </script>
        <?php else: ?>
            <div style="color: white; padding: 20px; text-align: center; font-size: 1.2em; margin-top: 50px;">
                ⚠️ Nenhum documento PDF liberado ou anexado a este processo.
            </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-right">
        <h2 style="margin-top:0; color: #002244; font-size: 1.5em; margin-bottom: 10px;">
            <?= htmlspecialchars($doc['protocol']) ?>
        </h2>
        
        <p style="margin: 5px 0; font-size: 0.95em;"><b>Assunto:</b> <?= htmlspecialchars($doc['name']) ?></p>
        <p style="margin: 5px 0; font-size: 0.95em;"><b>CPF/CNPJ:</b> <?= htmlspecialchars($doc['cpf_cnpj']) ?: '-' ?></p>
        <p style="margin: 5px 0; font-size: 0.95em;"><b>SOLEMP:</b> <?= htmlspecialchars($doc['solemp']) ?: '-' ?></p>
        
        <div style="margin: 10px 0 20px 0; border-bottom: 2px solid #002244; padding-bottom: 15px;">
            <b style="font-size: 0.95em;">Status:</b>
            <span style="background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 3px; font-weight:bold; font-size: 0.85em; display: inline-block; margin-left: 5px;">
                <?= htmlspecialchars($doc['status']) ?>
            </span>
        </div>

        <div class="info-box" style="background: #f1f3f5; max-height: 150px; overflow-y: auto;">
            <h4 style="margin-top: 0; color: #333; margin-bottom: 15px; font-size: 1em;">📎 Documentos do Processo (<?= count($files) ?>):</h4>
            <?php foreach ($files as $f): 
                $tipo = $f['file_type'];
                $nome = basename($f['filename']);
            ?>
                <div style="display: flex; align-items: center; background: white; border: 1px solid #ddd; border-radius: 4px; padding: 8px; margin-bottom: 8px;">
                    <a href="/<?= ltrim($f['filename'], '/') ?>" download="<?= htmlspecialchars($nome) ?>" style="background: #17a2b8; color: white; width: 30px; height: 30px; display: flex; justify-content: center; align-items: center; border-radius: 4px; text-decoration: none; font-size: 0.9em; margin-right: 12px; flex-shrink: 0;" title="Baixar">⬇️</a>
                    <div style="overflow: hidden; line-height: 1.2;">
                        <strong style="color: #002244; font-size: 0.85em;">[<?= $tipo ?>]</strong><br>
                        <span style="font-size: 0.8em; color: #555; word-wrap: break-word;"><?= htmlspecialchars($nome) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if(empty($files)): ?>
                <p style="color: #666; font-size: 0.85em; text-align: center;">Nenhum documento anexado.</p>
            <?php endif; ?>
        </div>

        <?php if ($role !== 'Usuário Comum'): ?>
            <h4 style="color: #d35400; margin-bottom: 8px; font-size: 1em;">📜 Histórico de Despachos:</h4>
            <div class="info-box" style="height: 130px; overflow-y: auto; font-family: monospace; font-size: 0.85em; color: #333; white-space: pre-wrap; line-height: 1.5; margin-bottom: 20px;">
<?= htmlspecialchars($doc['current_observation']) ?>
            </div>
        <?php endif; ?>

        <?php 
        if (in_array($role, ['Gestor_Financeiro', 'Gestor_Financeiro_Substituto', 'Chefe_Departamento', 'Agente_Fiscal', 'Ordenador_Despesas']) && (strpos($doc['status'], 'Caixa de Entrada') !== false || $doc['status'] === 'Devolvido - Gestor Financeiro')): 
        ?>
            <div>
                <h4 style="color: #d35400; margin-bottom: 8px; font-size: 1em;">✍️ Seu Parecer:</h4>
                
                <?php if (isset($_GET['aviso']) && $_GET['aviso'] === 'falta_parecer'): ?>
                    <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 0.9em; font-weight: bold; border-left: 4px solid #ffc107;">
                        ⚠️ Senhor Oficial, por favor, insira a justificativa no campo abaixo antes de rejeitar.
                    </div>
                <?php endif; ?>

                <form action="/process_action?id=<?= $doc['id'] ?>" method="POST" id="form-despacho">
                    <textarea name="new_observation" id="obs" placeholder="Digite aqui o despacho para o próximo nível..." style="width: 100%; height: 90px; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; resize: vertical; font-family: inherit; font-size: 0.95em;"></textarea>
                    
                    <div id="alerta-rejeicao-js" style="display: none; background: #fff3cd; color: #856404; padding: 8px 10px; border-radius: 4px; margin-top: 10px; font-size: 0.85em; font-weight: bold; border-left: 4px solid #ffc107;">
                        ⚠️ Para devolver o processo, é necessário preencher o motivo acima.
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="action" value="aprovar" class="btn-aprovar">✅ APROVAR</button>
                        <button type="submit" name="action" value="rejeitar" class="btn-devolver" onclick="return validarRejeicao(event)">❌ REJEITAR</button>
                    </div>
                </form>
            </div>
            
            <script>
            function validarRejeicao(e) {
                const obs = document.getElementById('obs').value.trim();
                const alerta = document.getElementById('alerta-rejeicao-js');
                if (obs === '') {
                    e.preventDefault(); // Trava a tela para não sair do lugar
                    alerta.style.display = 'block';
                    document.getElementById('obs').focus();
                    document.getElementById('obs').style.borderColor = '#e67e22';
                    return false;
                }
                alerta.style.display = 'none';
                return true;
            }
            </script>
        <?php endif; ?>
        
        <?php if ($role === 'Operador' && in_array($doc['status'], ['Devolvido - Operador', 'Arquivado', 'Cancelado', 'Anulado', 'Reforçado'])): ?>
            <a href="/edit?id=<?= $doc['id'] ?>" style="display: block; text-align: center; margin-top: 15px; background: #ffcc00; color: #002244; font-weight: bold; padding: 12px; border: 1px solid #d4ac00; border-radius: 4px; text-decoration: none;">✏️ Editar / Reiniciar Processo</a>
        <?php endif; ?>

        <a href="<?= $role === 'Usuário Comum' ? '/arquivo' : '/index' ?>" style="display: block; text-align: center; margin-top: 15px; background: #e9ecef; color: #002244; font-weight: bold; padding: 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">⬅ Voltar aos Resultados</a>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

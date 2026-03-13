<?php
$page_title = 'Visualizador de Processo';
$hide_navbar = true; // Oculta a barra padrão para aproveitar 100% da tela
require __DIR__ . '/partials/header.php';

$docCtrl = new \App\Controllers\DocumentController();
$dados = $docCtrl->getViewerData();

$doc = $dados['doc'];
$files = $dados['files'];
$role = $dados['role'];
?>
<style>
    /* Reset local para aproveitar a tela toda */
    .container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
    
    .viewer-container { display: flex; height: 100vh; overflow: hidden; background: #e9ecef; }
    
    /* REQUISITO: PDFs Empilhados (Área Esquerda) */
    .pdf-area-left { flex: 1; background: #525659; overflow-y: auto; padding: 20px; scroll-behavior: smooth; }
    .pdf-wrapper { margin-bottom: 40px; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
    .pdf-title { margin: 0 0 10px 0; color: #002244; font-size: 1.2em; text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; font-weight: bold; }
    iframe { width: 100%; height: 900px; border: 1px solid #ccc; background: white; }
    
    /* REQUISITO: Bloco de Ação na Direita */
    .sidebar-right { width: 420px; background: white; padding: 25px; overflow-y: auto; border-left: 3px solid #002244; display: flex; flex-direction: column; box-shadow: -4px 0 10px rgba(0,0,0,0.1); }
    
    /* Botões do Bloco Único */
    .btn-group { display: flex; gap: 10px; margin-top: 15px; }
    .btn-group button { flex: 1; padding: 12px; font-size: 1.05em; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; color: white; transition: 0.2s; }
    .btn-aprovar { background-color: #28a745; }
    .btn-aprovar:hover { background-color: #218838; }
    .btn-devolver { background-color: #dc3545; }
    .btn-devolver:hover { background-color: #c82333; }
</style>

<div class="viewer-container">
    
    <div class="pdf-area-left">
        <?php if (count($files) > 0): ?>
            <h3 style="color: white; text-align: center; margin-top: 0;">Role a página para ver todos os documentos ⬇️</h3>
            <?php foreach ($files as $f): ?>
                <div class="pdf-wrapper">
                    <h4 class="pdf-title">📄 <?= htmlspecialchars($f['file_type']) ?> - <?= htmlspecialchars(basename($f['filename'])) ?></h4>
                    <iframe src="/get_pdf?file=<?= urlencode($f['filename']) ?>#toolbar=1&navpanes=0"></iframe>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="color: white; padding: 20px; text-align: center; font-size: 1.2em; margin-top: 50px;">
                ⚠️ Nenhum documento PDF foi anexado a este processo.
            </div>
        <?php endif; ?>
    </div>

    <div class="sidebar-right">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="<?= $role === 'Usuário Comum' ? '/arquivo' : '/' ?>" style="color: white; background: #6c757d; text-decoration: none; font-weight: bold; padding: 8px 15px; border-radius: 4px;">⬅️ Voltar</a>
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 50px;">
        </div>
        
        <h3 style="margin-top:0; color: #002244; border-bottom: 2px solid #eee; padding-bottom: 10px;">Protocolo:<br><span style="color: #d32f2f; font-family: monospace; font-size: 1.2em;"><?= htmlspecialchars($doc['protocol']) ?></span></h3>
        
        <p style="margin: 5px 0;"><b>Assunto:</b><br> <?= htmlspecialchars($doc['name']) ?></p>
        <p style="margin: 5px 0;"><b>SOLEMP:</b> <?= htmlspecialchars($doc['solemp']) ?: '-' ?></p>
        <p style="margin: 5px 0;"><b>Status Atual:</b><br> <span style="background: #004488; color: white; padding: 4px 8px; border-radius: 3px; font-weight:bold; display: inline-block; margin-top: 5px;"><?= htmlspecialchars($doc['status']) ?></span></p>
        
        <hr style="border-top: 1px solid #ddd; margin: 20px 0; width: 100%;">

        <?php if (in_array($role, ['Enc_Financas', 'Ajudante_Encarregado', 'Chefe_Departamento', 'Vice_Diretor', 'Diretor']) && strpos($doc['status'], 'Caixa de Entrada') !== false): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 2px solid #002244; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h4 style="margin-top: 0; color: #002244; margin-bottom: 10px; text-align: center;">✍️ Despacho Tático</h4>
                
                <form action="/process_action?id=<?= $doc['id'] ?>" method="POST" id="form-despacho">
                    <textarea name="new_observation" id="obs" placeholder="Escreva aqui seu parecer (obrigatório em caso de devolução)..." style="width: 100%; height: 120px; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; resize: vertical; font-family: inherit; font-size: 1em;"></textarea>
                    
                    <div class="btn-group">
                        <button type="submit" name="action" value="aprovar" class="btn-aprovar" onclick="return confirm('Confirmar APROVAÇÃO do processo?');">
                            ✅ Aprovar
                        </button>
                        <button type="submit" name="action" value="rejeitar" class="btn-devolver" onclick="return validarDevolucao();">
                            ❌ Devolver
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <h4 style="color: #002244; margin-bottom: 10px;">📜 Histórico da Tramitação</h4>
        <div style="flex: 1; font-size: 0.85em; background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 4px; overflow-y: auto; white-space: pre-wrap; font-family: monospace; line-height: 1.5; color: #333; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
            <?= htmlspecialchars($doc['current_observation']) ?>
        </div>
    </div>
</div>

<script>
// Trava de segurança no Frontend para impedir devolução vazia
function validarDevolucao() {
    var obs = document.getElementById('obs').value.trim();
    if (obs === "") {
        alert("⚠️ REGRA DE NEGÓCIO: É obrigatório justificar no campo de despacho para DEVOLVER o processo ao Operador.");
        return false;
    }
    return confirm("ATENÇÃO: O processo será rejeitado e devolvido ao Operador. Deseja prosseguir?");
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
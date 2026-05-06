<?php
$page_title = 'Dashboard - Assinador BAMRJ';
require __DIR__ . '/partials/header.php';

$dashController = new \App\Controllers\DashboardController();
$dados = $dashController->getDashboardData();

$role = $dados['role'];
$is_substitute = $dados['is_substitute'];
$users = $dados['users'];
$documents = $dados['documents'];
$pre_protocol = $dados['pre_protocol'];
$inbox_count = $dados['inbox_count'];
?>

<style>
@keyframes pulso-suave {
    0%   { background-color: #ffcc00; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    50%  { background-color: #ffe066; box-shadow: 0 4px 15px rgba(255, 204, 0, 0.6); }
    100% { background-color: #ffcc00; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
}
.alerta-piscando {
    display: block !important;
    animation: pulso-suave 2.5s infinite ease-in-out !important;
    border: 1px solid #e6b800 !important;
}
</style>

<div id="alerta-novo-doc" style="display: none; background: #ffcc00; color: #002244; padding: 12px; text-align: center; font-weight: bold; margin-bottom: 20px; border-radius: 5px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" onclick="location.reload()">
    🔔 Há novos movimentos na sua caixa de entrada. Clique para atualizar.
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid #004488; flex-wrap: wrap; gap: 15px;">
    <div>
        <?php 
        $role_display = $role;
        if ($role === 'Gestor_Financeiro') $role_display = 'Gestor Financeiro';
        if ($role === 'Gestor_Financeiro_Substituto') $role_display = 'Gestor Financeiro Substituto';
        if ($role === 'Chefe_Departamento') $role_display = 'Chefe de Departamento';
        if ($role === 'Agente_Fiscal') $role_display = 'Agente Fiscal';
        if ($role === 'Ordenador_Despesas') $role_display = 'Ordenador de Despesas';
        ?>
        <h3 style="margin: 0; color: #002244;">Patente/Perfil: <span style="color: #666;"><?= htmlspecialchars($role_display) ?></span></h3>
        
        <?php if ($is_substitute): ?>
            <span style="background: #ffcc00; color: black; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 0.8em; display: inline-block; margin-top: 5px;">MODO SUBSTITUTO ATIVO</span>
        <?php endif; ?>
    </div>
    <div style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
        <?php if (in_array($role, ['Gestor_Financeiro', 'Gestor_Financeiro_Substituto', 'Chefe_Departamento', 'Agente_Fiscal'])): ?>
            <a href="/toggle_substitute" style="background: <?= $is_substitute ? '#dc3545' : '#ffcc00' ?>; color: <?= $is_substitute ? 'white' : '#002244' ?>; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold; text-align: center;">
                <?= $is_substitute ? '❌ Desativar Substituição' : '⚡ Ativar Substituição Superior' ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($role === 'Admin' && !empty($users)): ?>
<section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #00447c;">
    <h3 style="color: #00447c; margin-top: 0;">🛡️ Painel do Administrador</h3>
    <a href="/admin" class="btn btn-primary" style="margin-top: 10px;">📋 Gerenciar Utilizadores</a>
    <a href="/upload_legado" class="btn btn-info" style="margin-top: 10px;">📁 Upload Legado</a>
</section>
<?php endif; ?>

<?php if ($role === 'Operador'): ?>
<section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #28a745;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h3 style="margin: 0; color: #28a745;">⚡ Ações Rápidas do Operador</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/upload" class="btn btn-success">📄 Novo Processo</a>
            <a href="/upload_legado" class="btn btn-info">📁 Upload Legado</a>
        </div>
    </div>
    <?php if (!empty($pre_protocol)): ?>
    <div style="margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 4px; font-family: monospace; font-size: 0.9em;">
        Último protocolo gerado: <b><?= htmlspecialchars($pre_protocol) ?></b>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (!empty($documents)): ?>
<section style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #004488;">
    <h3 style="color: #004488; margin-top: 0;">📥 Caixa de Entrada (<?= count($documents) ?>)</h3>
    
    <div style="margin-bottom: 15px;">
        <input type="text" id="filtroDashboard" class="filtro-real" placeholder="🔍 Filtrar por Protocolo, NE, CNPJ ou Status..." onkeyup="filtrarDashboard()">
    </div>
    
    <div class="table-responsive">
        <table id="tabelaDashboard" style="width: 100%; border-collapse: collapse; min-width: 800px; font-size: 0.9em;">
            <tr style="background: #f8f9fa; border-bottom: 2px solid #002244; text-align: left;">
                <th style="padding: 10px;">Protocolo</th>
                <th style="padding: 10px;">NE / Assunto</th>
                <th style="padding: 10px;">CNPJ</th>
                <th style="padding: 10px;">Status</th>
                <th style="padding: 10px; text-align: right;">Ação</th>
            </tr>
            <?php foreach ($documents as $doc): ?>
            <tr class="linha-doc" style="border-bottom: 1px solid #eee; <?= ($doc['is_priority'] ?? false) ? 'background: #fff5f5;' : '' ?>">
                <td style="padding: 10px;"><code style="color: #d32f2f; font-weight: bold;"><?= htmlspecialchars($doc['protocol']) ?></code></td>
                <td style="padding: 10px;"><?= htmlspecialchars($doc['name']) ?> <?= ($doc['is_priority'] ?? false) ? '🚩' : '' ?></td>
                <td style="padding: 10px; font-family: monospace;"><?= htmlspecialchars($doc['cpf_cnpj'] ?? '-') ?></td>
                <td style="padding: 10px;">
                    <span class="badge <?= in_array($doc['status'], ['Arquivado', 'Reforçado']) ? 'badge-aviso' : 'badge-alerta' ?>">
                        <?= htmlspecialchars($doc['status']) ?>
                    </span>
                </td>
                <td style="padding: 10px; text-align: right;">
                    <a href="/view?id=<?= $doc['id'] ?>" class="btn btn-primary" style="font-size: 0.85em;">👁️ Ver</a>
                    <?php if (in_array($role, ['Operador', 'Admin']) && in_array($doc['status'], ['Devolvido pelo Chefe', 'Devolvido pelo Agente', 'Devolvido pelo Ordenador'])): ?>
                        <a href="/edit?id=<?= $doc['id'] ?>" class="btn btn-warning" style="font-size: 0.85em;">✏️ Corrigir</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>

<script>
function filtrarDashboard() {
    const termo = document.getElementById('filtroDashboard').value.toLowerCase();
    document.querySelectorAll('.linha-doc').forEach(linha => {
        const texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
}
</script>
<?php endif; ?>

<script>
// Radar de Inbox
setInterval(function() {
    fetch('/api/check_inbox')
        .then(r => r.json())
        .then(data => {
            const alerta = document.getElementById('alerta-novo-doc');
            if (data.count > 0) {
                alerta.style.display = 'block';
                alerta.classList.add('alerta-piscando');
            }
        });
}, 30000);
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
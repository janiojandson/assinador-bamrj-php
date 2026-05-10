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
/* 🗑️ FASE 4: Estilos do Modal de Cancelamento */
.modal-cancelar {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.modal-cancelar.ativo {
    display: flex;
}
.modal-cancelar-conteudo {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border-top: 5px solid #dc3545;
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
        <?php if ($role === 'Operador'): ?>
            <a href="/upload" style="background: #28a745; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold;">📤 Novo Processo</a>
            <a href="/upload_legado" style="background: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold;">📁 Upload Legado</a>
        <?php endif; ?>
        <a href="/arquivo" style="background: #004488; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold;">🗄️ Arquivo</a>
        <a href="/logout" style="background: #dc3545; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold;">🚪 Sair</a>
    </div>
</div>

<?php if ($role === 'Admin'): ?>
    <!-- VISÃO DO ADMIN -->
    <h2 style="color: #002244;">👥 Gerenciamento de Usuários</h2>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
            <tr style="background: #002244; color: white;">
                <th style="padding: 10px;">Nome</th>
                <th style="padding: 10px;">Usuário</th>
                <th style="padding: 10px;">Perfil</th>
                <th style="padding: 10px;">Ações</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;"><?= htmlspecialchars($u['name']) ?></td>
                <td style="padding: 10px; font-family: monospace;"><?= htmlspecialchars($u['username']) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($u['role']) ?></td>
                <td style="padding: 10px;">
                    <form method="POST" action="/admin/users" style="display: inline-flex; gap: 5px; align-items: center;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" style="padding: 5px; border-radius: 3px;">
                            <?php foreach (['Operador', 'Gestor_Financeiro', 'Gestor_Financeiro_Substituto', 'Chefe_Departamento', 'Agente_Fiscal', 'Ordenador_Despesas', 'Admin'] as $r): ?>
                                <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="password" name="password" placeholder="Nova Senha" style="padding: 5px; width: 120px; border-radius: 3px;">
                        <button type="submit" style="background: #004488; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">💾</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h3 style="margin-top: 20px; color: #002244;">➕ Criar Novo Usuário</h3>
        <form method="POST" action="/admin/users" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="action" value="create">
            <div><label style="font-weight: bold; font-size: 0.85em;">Nome:</label><br><input type="text" name="name" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;"></div>
            <div><label style="font-weight: bold; font-size: 0.85em;">Usuário:</label><br><input type="text" name="username" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 120px;"></div>
            <div><label style="font-weight: bold; font-size: 0.85em;">Senha:</label><br><input type="password" name="password" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 120px;"></div>
            <div><label style="font-weight: bold; font-size: 0.85em;">Perfil:</label><br>
                <select name="role" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <?php foreach (['Operador', 'Gestor_Financeiro', 'Gestor_Financeiro_Substituto', 'Chefe_Departamento', 'Agente_Fiscal', 'Ordenador_Despesas'] as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">➕ Criar</button>
        </form>
    </div>

<?php elseif ($role === 'Operador'): ?>
    <!-- VISÃO DO OPERADOR -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="color: #002244; margin: 0;">📋 Processos em Tramitação</h2>
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" placeholder="🔍 Buscar por Protocolo, CNPJ ou SOLEMP..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 350px;">
            <select name="ano" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?= $i ?>" <?= ($i == ($_GET['ano'] ?? date('Y'))) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" style="background: #004488; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">🔍</button>
        </form>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <?php if (empty($documents)): ?>
            <p style="text-align: center; color: #666; padding: 30px;">✅ Nenhum processo em tramitação.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                <tr style="background: #002244; color: white;">
                    <th style="padding: 10px;">Protocolo</th>
                    <th style="padding: 10px;">Assunto</th>
                    <th style="padding: 10px;">CNPJ / SOLEMP</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Prioridade</th>
                    <th style="padding: 10px;">Ações</th>
                </tr>
                <?php foreach ($documents as $doc): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-family: monospace; font-weight: bold; color: #004488;"><?= htmlspecialchars($doc['protocol']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($doc['name']) ?></td>
                    <td style="padding: 10px; font-size: 0.85em;"><?= htmlspecialchars($doc['cpf_cnpj'] ?? '') ?><br><?= htmlspecialchars($doc['solemp'] ?? '') ?></td>
                    <td style="padding: 10px;">
                        <?php
                        $status_color = '#004488';
                        if (str_contains($doc['status'], 'Devolvido')) $status_color = '#dc3545';
                        if (str_contains($doc['status'], 'Arquivado')) $status_color = '#28a745';
                        ?>
                        <span style="color: <?= $status_color ?>; font-weight: bold; font-size: 0.85em;"><?= htmlspecialchars($doc['status']) ?></span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <?= $doc['is_priority'] ? '<span style="background:#dc3545; color:white; padding:2px 6px; border-radius:3px; font-size:0.8em;">🔴 URG</span>' : 'Normal' ?>
                    </td>
                    <td style="padding: 10px;">
                        <a href="/view?id=<?= $doc['id'] ?>" style="background: #004488; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 0.85em; font-weight: bold;">Ver</a>
                        <?php if (in_array($doc['status'], ['Devolvido - Operador', 'Arquivado', 'Cancelado', 'Anulado', 'Reforçado'])): ?>
                            <a href="/edit?id=<?= $doc['id'] ?>" style="background: #ffcc00; color: #002244; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 0.85em; font-weight: bold; margin-left: 5px;">✏️ Editar</a>
                        <?php endif; ?>
                        <!-- 🗑️ FASE 4: Botão Cancelar Processo -->
                        <?php if (!in_array($doc['status'], ['Arquivado', 'Cancelado', 'Anulado', 'Reforçado'])): ?>
                            <button onclick="abrirModalCancelar(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['protocol']) ?>')" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; font-size: 0.85em; font-weight: bold; cursor: pointer; margin-left: 5px;">🗑️ Cancelar</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- VISÃO DOS OFICIAIS (Gestor, Chefe, Agente, Ordenador) -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="color: #002244; margin: 0;">📥 Caixa de Entrada</h2>
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" placeholder="🔍 Buscar..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 300px;">
            <select name="ano" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?= $i ?>" <?= ($i == ($_GET['ano'] ?? date('Y'))) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" style="background: #004488; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">🔍</button>
        </form>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <?php if (empty($documents)): ?>
            <p style="text-align: center; color: #28a745; font-weight: bold; padding: 30px; font-size: 1.2em;">✅ Caixa de Entrada Limpa!</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                <tr style="background: #002244; color: white;">
                    <th style="padding: 10px;">Protocolo</th>
                    <th style="padding: 10px;">Assunto</th>
                    <th style="padding: 10px;">CNPJ / SOLEMP</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Prioridade</th>
                    <th style="padding: 10px;">Ação</th>
                </tr>
                <?php foreach ($documents as $doc): ?>
                <tr style="border-bottom: 1px solid #eee; <?= $doc['is_priority'] ? 'background: #fff3cd;' : '' ?>">
                    <td style="padding: 10px; font-family: monospace; font-weight: bold; color: #004488;"><?= htmlspecialchars($doc['protocol']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($doc['name']) ?></td>
                    <td style="padding: 10px; font-size: 0.85em;"><?= htmlspecialchars($doc['cpf_cnpj'] ?? '') ?><br><?= htmlspecialchars($doc['solemp'] ?? '') ?></td>
                    <td style="padding: 10px; font-weight: bold; color: #004488; font-size: 0.85em;"><?= htmlspecialchars($doc['status']) ?></td>
                    <td style="padding: 10px; text-align: center;">
                        <?= $doc['is_priority'] ? '<span style="background:#dc3545; color:white; padding:2px 6px; border-radius:3px; font-size:0.8em;">🔴 URG</span>' : 'Normal' ?>
                    </td>
                    <td style="padding: 10px;">
                            <a href="/view?id=<?= $doc['id'] ?>" style="background: #004488; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 0.9em;">Abrir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- 🗑️ FASE 4: Modal de Cancelamento de Processo -->
<div id="modalCancelar" class="modal-cancelar">
    <div class="modal-cancelar-conteudo">
        <h3 style="margin-top: 0; color: #dc3545;">🗑️ Cancelar Processo</h3>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #dc3545;">
            <strong>⚠️ Ação Irreversível!</strong> Ao cancelar, o processo será encerrado e não poderá ser reaberto pelo fluxo normal.
        </div>
        <p>Protocolo: <b id="cancelarProtocolo" style="color: #dc3545;"></b></p>
        <form method="POST" action="/cancelar_processo" id="formCancelar">
            <input type="hidden" name="document_id" id="cancelarDocId">
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; color: #002244;">Motivo do Cancelamento (Obrigatório):</label>
                <textarea name="motivo_cancelamento" required rows="3" style="width: 100%; padding: 10px; border: 1px solid #dc3545; border-radius: 4px; font-size: 1em; margin-top: 5px;" placeholder="Descreva o motivo do cancelamento..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalCancelar()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">❌ Voltar</button>
                <button type="submit" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">🗑️ Confirmar Cancelamento</button>
            </div>
        </form>
    </div>
</div>

<script>
// 🗑️ FASE 4: Funções do Modal de Cancelamento
function abrirModalCancelar(docId, protocolo) {
    document.getElementById('cancelarDocId').value = docId;
    document.getElementById('cancelarProtocolo').textContent = protocolo;
    document.getElementById('modalCancelar').classList.add('ativo');
}

function fecharModalCancelar() {
    document.getElementById('modalCancelar').classList.remove('ativo');
}

// Fecha o modal ao clicar fora dele
document.getElementById('modalCancelar').addEventListener('click', function(e) {
    if (e.target === this) fecharModalCancelar();
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
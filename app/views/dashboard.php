<?php
// Instancia o Controller e busca os dados táticos para a View
$dashController = new \App\Controllers\DashboardController();
$dados = $dashController->getDashboardData();

// Extração de variáveis para facilitar o uso no HTML
$role = $dados['role'];
$is_substitute = $dados['is_substitute'];
$users = $dados['users'];
$documents = $dados['documents'];
$pre_protocol = $dados['pre_protocol'];
$inbox_count = $dados['inbox_count'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Assinador BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); background-color: #ffeb3b; }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body style="background-color: #f4f7f6; margin: 0; padding: 20px; font-family: Arial, sans-serif;">

<div style="max-width: 1200px; margin: 0 auto;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 60px;">
        <div>
            <a href="/logout" style="background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">Sair do Sistema</a>
        </div>
    </div>

    <div id="alerta-novo-doc" style="display: none; background: #ffcc00; color: #002244; padding: 12px; text-align: center; font-weight: bold; margin-bottom: 20px; border-radius: 5px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" onclick="limparAlerta()">
        🔔 Documento na caixa de entrada. Clique para atualizar.
    </div>

    <div class="flex-mobile" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #002244; color: white; padding: 15px; border-radius: 5px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="margin: 0;">👤 <?= htmlspecialchars($_SESSION['name'] ?? '') ?> [<?= $role === 'Enc_Financas' ? 'Enc. Finanças' : htmlspecialchars($role) ?>]</h3>
            <?php if ($is_substitute): ?>
                <span style="background: #ffcc00; color: black; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 0.8em;">MODO SUBSTITUTO ATIVO</span>
            <?php endif; ?>
        </div>
        <div class="flex-mobile-form" style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
            <?php if (in_array($role, ['Chefe_Departamento', 'Vice_Diretor'])): ?>
                <a href="/toggle_substitute" style="background: #ffcc00; color: black; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-weight: bold; text-align: center;">
                    <?= $is_substitute ? '⬅️ Voltar' : '⚡ Substituição Superior' ?>
                </a>
            <?php endif; ?>
            
            <form action="/" method="GET" class="flex-mobile-form" style="display: flex; gap: 5px;">
                <select name="ano" id="filtro-ano-dash" style="padding: 8px; border: none; border-radius: 3px; font-weight: bold; color: #002244;"></select>
                <input type="text" name="q" placeholder="Buscar por SOLEMP, CNPJ/CPF, Nome..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="padding: 8px; border: none; width: 280px; border-radius: 3px;">
                <button type="submit" style="padding: 8px 15px; background: #004488; color: white; border: none; border-radius: 3px; cursor: pointer;">🔍</button>
            </form>
        </div>
    </div>

    <?php if ($role === 'Admin'): ?>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 5px solid #dc3545; margin-bottom: 20px;">
        <h3>🛡️ Gestão de Utilizadores</h3>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd;">
            <h4 style="margin-top:0">➕ Cadastrar Novo Utilizador</h4>
            <form action="/admin/create_user" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;" class="flex-mobile-form">
                <input type="text" name="name" placeholder="Nome Completo" required style="padding: 8px; flex: 1.5;">
                <input type="text" name="username" placeholder="Utilizador (Login)" required style="padding: 8px; flex: 1;">
                <input type="password" name="password" placeholder="Senha Inicial" required style="padding: 8px; flex: 1;">
                <select name="role" required style="padding: 8px; flex: 1;">
                    <option value="Operador">Operador</option>
                    <option value="Enc_Financas">Enc. Finanças</option>
                    <option value="Ajudante_Encarregado">Ajudante do Encarregado</option>
                    <option value="Chefe_Departamento">Chefe de Departamento</option>
                    <option value="Vice_Diretor">Vice-Diretor</option>
                    <option value="Diretor">Diretor</option>
                    <option value="Usuário Comum">Usuário Comum</option>
                </select>
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 8px 20px; cursor: pointer; font-weight: bold;">Salvar</button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead><tr style="text-align: left; border-bottom: 2px solid #eee;"><th>Nome</th><th>Utilizador</th><th>Perfil</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><?= htmlspecialchars($u['name']) ?></td>
                        <td><b><?= htmlspecialchars($u['username']) ?></b></td>
                        <td>
                            <form action="/admin/edit_user" method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" onchange="this.form.submit()" style="padding: 5px;">
                                    <option value="Admin" <?= $u['role'] == 'Admin' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="Operador" <?= $u['role'] == 'Operador' ? 'selected' : '' ?>>Operador</option>
                                    <option value="Enc_Financas" <?= $u['role'] == 'Enc_Financas' ? 'selected' : '' ?>>Enc. Finanças</option>
                                    <option value="Ajudante_Encarregado" <?= $u['role'] == 'Ajudante_Encarregado' ? 'selected' : '' ?>>Ajudante do Encarregado</option>
                                    <option value="Chefe_Departamento" <?= $u['role'] == 'Chefe_Departamento' ? 'selected' : '' ?>>Chefe Departamento</option>
                                    <option value="Vice_Diretor" <?= $u['role'] == 'Vice_Diretor' ? 'selected' : '' ?>>Vice-Diretor</option>
                                    <option value="Diretor" <?= $u['role'] == 'Diretor' ? 'selected' : '' ?>>Diretor</option>
                                    <option value="Usuário Comum" <?= $u['role'] == 'Usuário Comum' ? 'selected' : '' ?>>Usuário Comum</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <?php if ($u['username'] !== 'admin'): ?>
                                <a href="/admin/delete?id=<?= $u['id'] ?>" style="color: red; text-decoration: none;" onclick="return confirm('Excluir utilizador?')">❌ Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'Operador'): ?>
    <div class="flex-mobile-form" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button onclick="document.getElementById('modal').style.display='block'" style="background: #28a745; color: white; padding: 12px 20px; border: none; cursor: pointer; font-weight: bold; border-radius: 4px;">➕ Iniciar Novo Processo</button>
        <a href="/arquivo" style="background: #17a2b8; color: white; padding: 12px 20px; text-decoration: none; cursor: pointer; font-weight: bold; border-radius: 4px; text-align: center;">🗄️ Acessar Arquivo Geral</a>
    </div>

    <div id="modal" style="display: none; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 10px solid #28a745; margin-bottom: 20px;">
        <h3>📄 Abertura de Demanda</h3>
        <form action="/upload" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="protocol" value="<?= $pre_protocol ?>">
            <p style="background: #fff3cd; padding: 10px; border-radius: 5px;"><strong>Protocolo Gerado:</strong> <?= $pre_protocol ?></p>
            
            <input type="text" name="process_name" placeholder="Assunto do Processo" required style="width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box;">
            
            <div class="flex-mobile" style="display: flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="cpf_cnpj" placeholder="CPF ou CNPJ (Somente números)" style="flex: 1; padding: 10px; box-sizing: border-box;">
                <input type="text" name="solemp" placeholder="Nº da SOLEMP (Opcional)" style="flex: 1; padding: 10px; box-sizing: border-box;">
            </div>

            <label style="display: block; margin-bottom: 15px;"><input type="checkbox" name="priority" value="1"> 🚩 Processo Prioritário</label>
            
            <div class="flex-mobile" style="display: flex; gap: 20px; margin-bottom: 15px;">
                <div style="flex: 1; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <label><b>Minutas (PDF):</b></label><br>
                    <input type="file" id="m-in" accept="application/pdf" multiple style="margin-top:5px; max-width: 100%;">
                    <ul id="m-list" style="font-size: 0.85em; color: #666; padding-left: 20px; word-break: break-all;"></ul>
                    <input type="file" name="minutas[]" id="m-hidden" multiple style="display: none;">
                </div>
                <div style="flex: 1; background: #f8f9fa; padding: 10px; border-radius: 5px;">
                    <label><b>Anexos (PDF):</b></label><br>
                    <input type="file" id="a-in" accept="application/pdf" multiple style="margin-top:5px; max-width: 100%;">
                    <ul id="a-list" style="font-size: 0.85em; color: #666; padding-left: 20px; word-break: break-all;"></ul>
                    <input type="file" name="anexos[]" id="a-hidden" multiple style="display: none;">
                </div>
            </div>
            <textarea name="observation" placeholder="Observações iniciais..." style="width: 100%; height: 60px; margin-bottom: 10px; padding: 5px; box-sizing: border-box;"></textarea>
            <div class="flex-mobile-form" style="display: flex; gap: 10px;">
                <button type="submit" style="background: #003366; color: white; padding: 10px 25px; border: none; cursor: pointer; font-weight: bold; flex: 2;">Gerar e Tramitar</button>
                <button type="button" onclick="location.reload()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; cursor: pointer; flex: 1;">Cancelar</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($role !== 'Admin'): ?>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <?php if (empty($documents)): ?>
            <h3 style="text-align: center; color: #666;">Nenhum processo pendente na sua caixa de entrada.</h3>
        <?php else: ?>
            <h3>📂 Processos Encontrados/Na Caixa</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd; text-align: left;">
                            <th style="padding: 10px;">Prior.</th>
                            <th style="padding: 10px;">Protocolo</th>
                            <th style="padding: 10px;">Assunto</th>
                            <th style="padding: 10px;">CPF/CNPJ</th>
                            <th style="padding: 10px;">SOLEMP</th> 
                            <th style="padding: 10px;">Status</th>
                            <th style="padding: 10px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr style="border-bottom: 1px solid #eee; <?= $doc['is_priority'] ? 'background: #fff5f5;' : '' ?>">
                            <td style="padding: 10px; text-align: center;"><?= $doc['is_priority'] ? '🚩' : '🏳️' ?></td>
                            <td style="padding: 10px;"><code><?= htmlspecialchars($doc['protocol']) ?></code></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($doc['name']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($doc['cpf_cnpj']) ?: '-' ?></td>
                            <td style="padding: 10px;"><strong><?= htmlspecialchars($doc['solemp']) ?: '-' ?></strong></td> 
                            <td style="padding: 10px;">
                                <?php
                                $statusBg = '#e2e3e5'; $statusColor = '#383d41';
                                if ($doc['status'] === 'Devolvido - Operador') { $statusBg = '#f8d7da'; $statusColor = '#721c24'; }
                                elseif ($doc['status'] === 'Aguardando Empenho - Operador') { $statusBg = '#d4edda'; $statusColor = '#155724'; }
                                elseif (in_array($doc['status'], ['Cancelado', 'Anulado'])) { $statusBg = '#343a40'; $statusColor = 'white'; }
                                elseif ($doc['status'] === 'Reforçado') { $statusBg = '#17a2b8'; $statusColor = 'white'; }
                                ?>
                                <span style="font-size: 0.85em; padding: 4px 8px; border-radius: 4px; font-weight: bold; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                                    <?= htmlspecialchars($doc['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
                                <a href="/view?id=<?= $doc['id'] ?>" style="background: #003366; color: white; padding: 5px 12px; text-decoration: none; border-radius: 3px; font-weight: bold; font-size: 0.9em;">Abrir</a>
                                
                                <?php if ($role === 'Operador'): ?>
                                    <?php if ($doc['status'] === 'Devolvido - Operador'): ?>
                                        <a href="/edit?id=<?= $doc['id'] ?>" style="background: #ffcc00; color: #002244; padding: 5px 12px; text-decoration: none; border-radius: 3px; font-weight: bold; font-size: 0.9em;">✏️ Editar</a>
                                    <?php endif; ?>

                                    <form action="/cancel?id=<?= $doc['id'] ?>" method="POST" onsubmit="return confirm('Deseja realmente cancelar este processo?');" style="margin: 0;">
                                        <button type="submit" style="background: #dc3545; color: white; padding: 5px 12px; border: none; border-radius: 3px; cursor: pointer; font-size: 0.9em; font-weight: bold;">Cancelar</button>
                                    </form>
                                    
                                    <?php if ($doc['status'] === 'Aguardando Empenho - Operador'): ?>
                                        <form action="/upload_ne?id=<?= $doc['id'] ?>" method="POST" enctype="multipart/form-data" style="margin: 0; display: flex; gap: 5px; align-items: center; background: #e9ecef; padding: 5px; border-radius: 4px;">
                                            <select name="final_status" required style="padding: 5px; font-size: 0.8em; border-radius: 3px; border: 1px solid #ccc;">
                                                <option value="Arquivado">Arquivar</option>
                                                <option value="Reforçado">Reforçado</option>
                                                <option value="Anulado">Anulado</option>
                                                <option value="Cancelado">Cancelado</option>
                                            </select>
                                            <div style="position: relative;">
                                                <input type="file" id="ne-in-<?= $doc['id'] ?>" name="nota_empenho" required accept="application/pdf" style="display: none;" onchange="gerenciarNE(<?= $doc['id'] ?>)">
                                                <button type="button" onclick="document.getElementById('ne-in-<?= $doc['id'] ?>').click()" style="background: #6c757d; color: white; padding: 5px 10px; border-radius: 3px; border: none; cursor: pointer; font-size: 0.85em; font-weight: bold;">📎 NE</button>
                                            </div>
                                            <div id="ne-view-<?= $doc['id'] ?>" style="display: none; align-items: center; gap: 5px; background: #fff3cd; padding: 2px 8px; border-radius: 3px; border: 1px solid #ffeeba;">
                                                <span id="ne-name-<?= $doc['id'] ?>" style="font-size: 0.8em; color: #856404; max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                                                <b onclick="cancelarNE(<?= $doc['id'] ?>)" style="color: #dc3545; cursor: pointer; font-size: 1em;">[X]</b>
                                            </div>
                                            <button type="submit" id="ne-btn-<?= $doc['id'] ?>" style="background: #28a745; color: white; padding: 5px 12px; border: none; border-radius: 3px; cursor: pointer; font-size: 0.9em; font-weight: bold; display: none;">Enviar</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?> 

</div>

<script>
// Script de Anos no Filtro
const selectAnoDash = document.getElementById('filtro-ano-dash');
const anoAtualDash = new Date().getFullYear();
const anoMaximoDash = anoAtualDash < 2026 ? 2026 : anoAtualDash;
const urlParams = new URLSearchParams(window.location.search);
const anoPesquisado = urlParams.get('ano');

for (let ano = 2026; ano <= anoMaximoDash; ano++) {
    let opt = document.createElement('option');
    opt.value = ano; opt.innerHTML = ano;
    if (anoPesquisado && parseInt(anoPesquisado) === ano) { opt.selected = true; } 
    else if (!anoPesquisado && ano === anoMaximoDash) { opt.selected = true; }
    selectAnoDash.appendChild(opt);
}

// Scripts de Manipulação de NE (Nota de Empenho)
function gerenciarNE(docId) {
    const input = document.getElementById('ne-in-' + docId);
    const view = document.getElementById('ne-view-' + docId);
    const nameSpan = document.getElementById('ne-name-' + docId);
    const btnSubmit = document.getElementById('ne-btn-' + docId);
    if (input.files && input.files[0]) {
        nameSpan.innerText = input.files[0].name;
        view.style.display = 'flex';
        btnSubmit.style.display = 'block';
    }
}

function cancelarNE(docId) {
    const input = document.getElementById('ne-in-' + docId);
    const view = document.getElementById('ne-view-' + docId);
    const btnSubmit = document.getElementById('ne-btn-' + docId);
    input.value = ''; 
    view.style.display = 'none';
    btnSubmit.style.display = 'none';
}

function limparAlerta() {
    location.reload();
}

// Manipulação de Múltiplos Arquivos no Modal
const dtM = new DataTransfer(), dtA = new DataTransfer();
function setupFiles(inId, hidId, listId, dt) {
    const inp = document.getElementById(inId), hid = document.getElementById(hidId), list = document.getElementById(listId);
    if(!inp) return;
    inp.addEventListener('change', () => { for(let f of inp.files) dt.items.add(f); renderFiles(list, hid, dt); });
}

function renderFiles(list, hid, dt) {
    list.innerHTML = ''; hid.files = dt.files;
    Array.from(dt.files).forEach((f, i) => {
        const li = document.createElement('li');
        li.innerHTML = f.name + ' <b style="cursor:pointer;color:red;margin-left:10px">X</b>';
        li.querySelector('b').onclick = () => { dt.items.remove(i); renderFiles(list, hid, dt); };
        list.appendChild(li);
    });
}
setupFiles('m-in', 'm-hidden', 'm-list', dtM);
setupFiles('a-in', 'a-hidden', 'a-list', dtA);
</script>

</body>
</html>
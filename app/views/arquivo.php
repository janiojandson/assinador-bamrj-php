<?php
// Aciona o Controller para buscar a inteligência de dados
$archiveController = new \App\Controllers\ArchiveController();
$dados = $archiveController->getArchiveData();

$role = $dados['role'];
$search_query = $dados['search_query'];
$documents = $dados['documents'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Arquivo Geral - Assinador BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body style="background-color: #f4f7f6; margin: 0; padding: 20px; font-family: Arial, sans-serif;">

<div style="max-width: 1200px; margin: 0 auto;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 60px;">
        <div>
            <?php if ($role === 'Usuário Comum'): ?>
                <a href="/logout" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">Sair / Acesso Militar</a>
            <?php else: ?>
                <a href="/" style="background: #004488; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">⬅️ Voltar ao Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-mobile" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #002244; color: white; padding: 15px; border-radius: 5px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h3 style="margin: 0;">🗄️ Arquivo Geral de Processos</h3>
            <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #ccc;">Consulta de Notas de Empenho e Processos Finalizados</p>
        </div>
        <div class="flex-mobile-form" style="display: flex; gap: 10px; width: 100%; justify-content: flex-end;">
            <form action="/arquivo" method="GET" class="flex-mobile-form" style="display: flex; gap: 5px;">
                <select name="ano" id="filtro-ano-arq" style="padding: 8px; border: none; border-radius: 3px; font-weight: bold; color: #002244;"></select>
                <input type="text" name="q" placeholder="Buscar por SOLEMP, CNPJ/CPF, Nome..." value="<?= htmlspecialchars($search_query) ?>" style="padding: 8px; border: none; width: 280px; border-radius: 3px;">
                <button type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">🔍 Buscar no Arquivo</button>
            </form>
        </div>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        
        <?php if ($role === 'Usuário Comum' && empty($documents) && empty($search_query)): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <h3 style="color: #666;">Bem-vindo à Consulta Pública do Assinador BAMRJ.</h3>
                <p style="color: #888;">Utilize a barra de pesquisa acima para buscar Notas de Empenho e Processos Finalizados através do CPF/CNPJ ou número da SOLEMP.</p>
            </div>
            
        <?php elseif (empty($documents) && !empty($search_query)): ?>
            <h3 style="text-align: center; color: #dc3545;">Nenhum processo finalizado encontrado para a busca "<?= htmlspecialchars($search_query) ?>".</h3>
            
        <?php elseif (empty($documents)): ?>
            <h3 style="text-align: center; color: #666;">O arquivo está vazio para o ano selecionado.</h3>
            
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #ddd; text-align: left;">
                            <th style="padding: 10px;">Protocolo</th>
                            <th style="padding: 10px;">Assunto</th>
                            <th style="padding: 10px;">CPF/CNPJ</th>
                            <th style="padding: 10px;">SOLEMP</th> 
                            <th style="padding: 10px;">Status Final</th>
                            <th style="padding: 10px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 10px;"><code><?= htmlspecialchars($doc['protocol']) ?></code></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($doc['name']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($doc['cpf_cnpj']) ?: '-' ?></td>
                            <td style="padding: 10px;"><strong><?= htmlspecialchars($doc['solemp']) ?: '-' ?></strong></td> 
                            <td style="padding: 10px;">
                                <?php
                                $statusBg = '#e2e3e5'; $statusColor = '#383d41';
                                if (in_array($doc['status'], ['Cancelado', 'Anulado'])) { $statusBg = '#343a40'; $statusColor = 'white'; }
                                elseif ($doc['status'] === 'Reforçado') { $statusBg = '#17a2b8'; $statusColor = 'white'; }
                                elseif ($doc['status'] === 'Arquivado') { $statusBg = '#28a745'; $statusColor = 'white'; }
                                ?>
                                <span style="font-size: 0.85em; padding: 4px 8px; border-radius: 4px; font-weight: bold; background: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                                    <?= htmlspecialchars($doc['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 10px;">
                                <a href="/view?id=<?= $doc['id'] ?>" style="background: #003366; color: white; padding: 5px 12px; text-decoration: none; border-radius: 3px; font-weight: bold; font-size: 0.9em;">📄 Visualizar PDF</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Script de Controle de Anos
const selectAnoArq = document.getElementById('filtro-ano-arq');
const anoAtualArq = new Date().getFullYear();
const anoMaximoArq = anoAtualArq < 2026 ? 2026 : anoAtualArq;
const urlParams = new URLSearchParams(window.location.search);
const anoPesquisado = urlParams.get('ano');

for (let ano = 2026; ano <= anoMaximoArq; ano++) {
    let opt = document.createElement('option');
    opt.value = ano; opt.innerHTML = ano;
    if (anoPesquisado && parseInt(anoPesquisado) === ano) { opt.selected = true; } 
    else if (!anoPesquisado && ano === anoMaximoArq) { opt.selected = true; }
    selectAnoArq.appendChild(opt);
}
</script>

</body>
</html>
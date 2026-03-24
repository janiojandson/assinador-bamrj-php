<?php
$page_title = 'BAMRJ | Arquivo Histórico';
require __DIR__ . '/partials/header.php';

$archiveCtrl = new \App\Controllers\ArchiveController();

// 🟢 Recebemos o pacote completo do Controller
$dados_arquivo = $archiveCtrl->getArchiveData();

// 🟢 Extraímos APENAS a lista de documentos para a tabela não quebrar
$documents = $dados_arquivo['documents'] ?? [];

// 🟢 Recuperamos a role para a blindagem de visualização
$role = $_SESSION['role'] ?? 'Usuário Comum';
?>

<?php if ($role === 'Usuário Comum'): ?>
    <div style="background: #00447c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin: 0; font-size: 1.5em;">🔍 Consulta Pública de Processos</h2>
        <p style="margin: 5px 0 0 0; font-size: 0.9em;">Acompanhe em tempo real a tramitação do seu processo. O acesso à Nota de Empenho só é libertada após as assinaturas.</p>
    </div>
<?php endif; ?>

<section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #00447c;">
    <form method="GET" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 250px;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block; color: #333;">Pesquisar (Protocolo, CPF/CNPJ ou SOLEMP):</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Digite para buscar e rastrear o processo..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block; color: #333;">Ano do Registo:</label>
            <select name="ano" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="todos" <?php echo (($_GET['ano'] ?? '') === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>
                <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($i == ($_GET['ano'] ?? date('Y'))) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" style="padding: 10px 20px; background: #00447c; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; height: 40px;">PESQUISAR</button>
        <?php if (!empty($_GET['q'])): ?>
            <a href="/arquivo" style="padding: 10px 20px; background: #6c757d; color: white; border-radius: 4px; font-weight: bold; text-decoration: none; height: 40px; box-sizing: border-box; display: inline-flex; align-items: center;">Limpar</a>
        <?php endif; ?>
    </form>
</section>

<div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                <th style="padding: 15px; color: #00447c;">Protocolo</th>
                <th style="padding: 15px; color: #00447c;">NE</th>
                <th style="padding: 15px; color: #00447c;">Status Atual</th> 
                <th style="padding: 15px; color: #00447c;">Data de Criação</th>
                <th style="padding: 15px; color: #00447c; text-align: center;">Ações / Acesso</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $doc): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($doc['protocol']); ?></strong></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($doc['name']); ?></td>
                <td style="padding: 12px;">
                    <span style="background: <?php echo (strpos($doc['status'], 'Caixa') !== false) ? '#17a2b8' : '#6c757d'; ?>; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em; font-weight: bold; display: inline-block;">
                        <?php echo htmlspecialchars($doc['status']); ?>
                    </span>
                </td>
                <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                <td style="padding: 12px; text-align: center;">
                    <?php 
                    // 🟢 REGRA 6: Verificação de Segurança para Acesso aos Ficheiros
                    $is_finished = in_array($doc['status'], ['Arquivado', 'Reforçado']);
                    
                    if ($role === 'Usuário Comum' && !$is_finished): 
                    ?>
                        <div style="color: #856404; background: #fff3cd; padding: 6px 10px; border-radius: 4px; font-size: 0.85em; display: inline-block; border: 1px solid #ffeeba; text-align: center; font-weight: bold;" title="O processo encontra-se a circular pelas instâncias de aprovação.">
                            🔒 Em Tramitação
                        </div>
                    <?php else: ?>
                        <a href="/view?id=<?php echo $doc['id']; ?>" style="font-weight: bold; color: white; background: #00447c; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.9em;">Ver Detalhes</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($documents) && $role === 'Usuário Comum' && empty($_GET['q'])): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #555;">👆 Utilize a barra de pesquisa acima.</td></tr>
            <?php elseif (empty($documents)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #dc3545;"><strong>❌ Nenhum registo encontrado para esta consulta.</strong> Verifique a numeração ou tente alterar o "Ano do Registo" para "Todos os Anos".</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
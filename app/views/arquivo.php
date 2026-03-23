<?php
$page_title = 'BAMRJ | Arquivo Histórico';
require __DIR__ . '/partials/header.php';

$archiveCtrl = new \App\Controllers\ArchiveController();

// 🟢 CORREÇÃO APLICADA AQUI: Recebemos o pacote completo do Controller
$dados_arquivo = $archiveCtrl->getArchiveData();

// 🟢 E extraímos APENAS a lista de documentos para a tabela não quebrar
$documents = $dados_arquivo['documents'] ?? [];

// 🟢 Recuperamos a role para a blindagem de visualização
$role = $_SESSION['role'] ?? 'Usuário Comum';
?>

<section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #00447c;">
    <form method="GET" style="display: flex; gap: 10px; align-items: flex-end;">
        <div style="flex: 2;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block;">Pesquisar (Nome, Protocolo, CPF ou SOLEMP):</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Digite para buscar..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        <div style="flex: 1;">
            <label style="font-weight: bold; margin-bottom: 5px; display: block;">Ano do Registo:</label>
            <select name="ano" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="todos" <?php echo (($_GET['ano'] ?? '') === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>
                <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($i == ($_GET['ano'] ?? date('Y'))) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" style="padding: 10px 20px; background: #00447c; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; height: 40px;">PESQUISAR</button>
    </form>
</section>

<div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                <th style="padding: 15px; color: #00447c;">Protocolo</th>
                <th style="padding: 15px; color: #00447c;">Nome/Favorecido</th>
                <th style="padding: 15px; color: #00447c;">Status Final</th>
                <th style="padding: 15px; color: #00447c;">Data de Criação</th>
                <th style="padding: 15px; color: #00447c;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documents as $doc): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($doc['protocol']); ?></strong></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($doc['name']); ?></td>
                <td style="padding: 12px;"><span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.85em; font-weight: bold;"><?php echo htmlspecialchars($doc['status']); ?></span></td>
                <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                <td style="padding: 12px;">
                    <a href="/view?id=<?php echo $doc['id']; ?>" style="font-weight: bold; color: white; background: #00447c; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.9em;">Ver Detalhes</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($documents) && $role === 'Usuário Comum' && empty($_GET['q'])): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px;">Utilize a busca acima para localizar um processo arquivado.</td></tr>
            <?php elseif (empty($documents)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #e74c3c;"><strong>Nenhum registo encontrado para esta consulta.</strong> Tente alterar o "Ano do Registo" para "Todos os Anos".</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
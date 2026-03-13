<?php
$archiveCtrl = new \App\Controllers\ArchiveController();
$documents = $archiveCtrl->getArchiveData();
$role = $_SESSION['role'] ?? 'Usuário Comum';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Arquivo Geral - BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <header>
        <div><strong>BAMRJ</strong> | Arquivo Histórico</div>
        <a href="/index" style="color: white; text-decoration: none;">Voltar ao Dashboard</a>
    </header>

    <main class="container">
        <section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <form method="GET" style="display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 2;">
                    <label>Pesquisar (Nome, Protocolo, CPF ou SOLEMP):</label>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Digite para buscar...">
                </div>
                <div style="flex: 1;">
                    <label>Ano:</label>
                    <select name="ano">
                        <?php for($i = date('Y'); $i >= 2024; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == ($_GET['ano'] ?? date('Y'))) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary">PESQUISAR</button>
            </form>
        </section>

        <table>
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Nome/Favorecido</th>
                    <th>Status Final</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($doc['protocol']); ?></strong></td>
                    <td><?php echo htmlspecialchars($doc['name']); ?></td>
                    <td><span class="status-badge badge-process"><?php echo htmlspecialchars($doc['status']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                    <td>
                        <a href="/view?id=<?php echo $doc['id']; ?>" style="font-weight: bold; color: #00447c; text-decoration: none;">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($documents) && $role === 'Usuário Comum' && empty($_GET['q'])): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 30px;">Utilize a busca acima para localizar um processo arquivado.</td></tr>
                <?php elseif (empty($documents)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 30px;">Nenhum registro encontrado para esta consulta.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
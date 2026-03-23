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
    <header style="background: #00447c; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="/static/img/brasao_bamrj.png" alt="BAMRJ" style="height: 40px;">
            <strong>BAMRJ | Arquivo Histórico</strong>
        </div>
        <div>
            <?php if ($role !== 'Usuário Comum'): ?>
                <a href="/index" style="color: white; text-decoration: none; border: 1px solid white; padding: 6px 15px; border-radius: 4px; font-weight: bold; background: rgba(255,255,255,0.1);">🏠 INÍCIO / DASHBOARD</a>
            <?php else: ?>
                <a href="/login" style="color: white; text-decoration: none; border: 1px solid white; padding: 6px 15px; border-radius: 4px; font-weight: bold;">Acesso Restrito</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <section style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <form method="GET" style="display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 2;">
                    <label>Pesquisar (Nome, Protocolo, CPF ou SOLEMP):</label>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Digite para buscar...">
                </div>
                <div style="flex: 1;">
                    <label>Ano do Registo:</label>
                    <select name="ano">
                        <option value="todos" <?php echo (($_GET['ano'] ?? '') === 'todos') ? 'selected' : ''; ?>>Todos os Anos</option>
                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
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
                    <tr><td colspan="5" style="text-align: center; padding: 30px; color: #e74c3c;"><strong>Nenhum registo encontrado para esta consulta.</strong> Tente alterar o "Ano do Registo" para "Todos os Anos".</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
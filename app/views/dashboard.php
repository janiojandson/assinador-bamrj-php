<?php
$docCtrl = new \App\Controllers\DocumentController();
$documents = $docCtrl->getDashboardData();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Assinador BAMRJ</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        .priority-row { background-color: #fff5f5; border-left: 5px solid red; }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; }
        .badge-waiting { background: #ffd900; color: #000; }
        .badge-process { background: #00447c; color: #fff; }
    </style>
</head>
<body>
    <header style="background: #00447c; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>BAMRJ - Sistema de Assinaturas</strong> | 
            Usuário: <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)
        </div>
        <a href="/logout" style="color: white; text-decoration: none; border: 1px solid white; padding: 5px 10px; border-radius: 4px;">Sair</a>
    </header>

    <main style="padding: 20px;">
        <h2>Caixa de Entrada Tática</h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                    <th style="padding: 12px;">Protocolo</th>
                    <th style="padding: 12px;">Processo</th>
                    <th style="padding: 12px;">Status</th>
                    <th style="padding: 12px;">Data</th>
                    <th style="padding: 12px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr style="border-bottom: 1px solid #eee;" class="<?php echo $doc['is_priority'] ? 'priority-row' : ''; ?>">
                    <td style="padding: 12px;"><strong><?php echo htmlspecialchars($doc['protocol']); ?></strong></td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($doc['name']); ?></td>
                    <td style="padding: 12px;">
                        <span class="status-badge badge-process"><?php echo htmlspecialchars($doc['status']); ?></span>
                    </td>
                    <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?></td>
                    <td style="padding: 12px;">
                        <a href="/view?id=<?php echo $doc['id']; ?>" style="text-decoration: none; color: #00447c; font-weight: bold;">Visualizar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($documents)): ?>
                <tr><td colspan="5" style="padding: 20px; text-align: center; color: #666;">Nenhum processo pendente na sua caixa.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
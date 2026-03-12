<?php include __DIR__ . '/partials/header.php'; ?>

<div class="card">
    <h3>👤 Bem-vindo, <?= htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8') ?> [<?= htmlspecialchars($_SESSION['role']) ?>]</h3>
    
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <button onclick="document.getElementById('modal').style.display='block'" class="btn-success">
            ➕ Iniciar Novo Processo
        </button>
    </div>

    <table>
        <tr><th>Protocolo</th><th>Assunto</th><th>Status</th></tr>
        <?php foreach ($documents as $doc): ?>
            <tr>
                <td><?= htmlspecialchars($doc['protocol']) ?></td>
                <td><?= htmlspecialchars($doc['name']) ?></td>
                <td><?= htmlspecialchars($doc['status']) ?></td>
            </tr>
        <?php endphp; ?>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
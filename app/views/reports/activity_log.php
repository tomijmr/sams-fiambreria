<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Bitacora de Actividad</h1>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-sm mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Accion</th>
                    <th>Entidad</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($entries)): ?>
                <tr><td colspan="5" class="text-muted text-center py-3">Sin actividad registrada todavia.</td></tr>
            <?php endif; ?>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['created_at']) ?></td>
                    <td><?= htmlspecialchars($entry['user_name']) ?></td>
                    <td><?= htmlspecialchars($entry['action']) ?></td>
                    <td><?= htmlspecialchars($entry['entity']) ?><?= $entry['entity_id'] ? ' #' . (int)$entry['entity_id'] : '' ?></td>
                    <td><?= htmlspecialchars($entry['details']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

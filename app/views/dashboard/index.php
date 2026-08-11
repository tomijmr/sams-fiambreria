<?php require __DIR__ . '/../layouts/header.php'; ?>
<h1 class="h3 mb-4">Panel de Control</h1>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-kpi">
            <div class="card-body">
                <h2 class="h6 text-muted">Ventas de Hoy</h2>
                <p class="display-6 mb-0">$ <?= number_format($salesToday, 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-kpi">
            <div class="card-body">
                <h2 class="h6 text-muted">Tickets de Hoy</h2>
                <p class="display-6 mb-0"><?= (int)$ticketsToday ?></p>
            </div>
        </div>
    </div>
    <?php if (Auth::isAdmin()): ?>
    <div class="col-md-4">
        <div class="card card-kpi">
            <div class="card-body">
                <h2 class="h6 text-muted">Gastos del Mes</h2>
                <p class="display-6 mb-0">$ <?= number_format($expensesMonth, 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Alerta de Stock Bajo</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['unit_type'] === 'weight' ? 'Por peso' : 'Por unidad' ?></td>
                        <td>
                            <?= $p['unit_type'] === 'weight'
                                ? number_format((float)$p['stock_kg'], 3, ',', '.') . ' kg'
                                : (int)$p['stock_units'] . ' un.' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

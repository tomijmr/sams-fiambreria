<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Reporte de Caja Diario</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/reports/daily-cash" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Ver reporte</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-kpi h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Ingresos (Ventas)</h2>
                <p class="fs-4 mb-0">$ <?= number_format($incomeTotal, 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-kpi h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Egresos (Gastos)</h2>
                <p class="fs-4 mb-0">$ <?= number_format($expenseTotal, 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-kpi h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Pagos a Proveedores</h2>
                <p class="fs-4 mb-0">$ <?= number_format($providerPayments, 2, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-kpi h-100">
            <div class="card-body">
                <h2 class="h6 text-muted">Resultado Neto</h2>
                <p class="fs-4 mb-0 <?= $netTotal < 0 ? 'text-danger' : 'text-success' ?>">
                    $ <?= number_format($netTotal, 2, ',', '.') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Resumen de ventas por medio de pago</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Medio</th>
                            <th>Operaciones</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paymentSummary)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin ventas para la fecha.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($paymentSummary as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($row['payment_method'])) ?></td>
                                <td><?= (int)$row['qty'] ?></td>
                                <td>$ <?= number_format((float)$row['total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Detalle de egresos</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Categoria</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expenses)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin gastos para la fecha.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= htmlspecialchars($expense['description']) ?></td>
                                <td><?= htmlspecialchars($expense['category']) ?></td>
                                <td>$ <?= number_format((float)$expense['amount'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                Otros gastos (no proveedor): $ <?= number_format($otherExpenses, 2, ',', '.') ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Ventas del dia</div>
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th># Venta</th>
                    <th>Hora</th>
                    <th>Medio</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-3">No se registran ventas en esta fecha.</td></tr>
                <?php endif; ?>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= (int)$sale['id'] ?></td>
                        <td><?= htmlspecialchars(date('H:i', strtotime($sale['created_at']))) ?></td>
                        <td><?= htmlspecialchars(ucfirst($sale['payment_method'])) ?></td>
                        <td>$ <?= number_format((float)$sale['total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

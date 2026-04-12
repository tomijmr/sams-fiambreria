<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Historial de Ventas</h1>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/sales/pos">Ir a Venta Rapida</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/sales/history" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-3 text-md-end">
                <div class="small text-muted">Cantidad ventas: <?= count($sales) ?></div>
                <div class="fs-5">Total rango: <strong>$ <?= number_format($totalRange, 2, ',', '.') ?></strong></div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Ventas encontradas</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th># Venta</th>
                    <th>Fecha y Hora</th>
                    <th>Medio de Pago</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No hay ventas en el rango seleccionado.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= (int)$sale['id'] ?></td>
                        <td><?= htmlspecialchars($sale['created_at']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($sale['payment_method'])) ?></td>
                        <td><?= (int)$sale['items_count'] ?></td>
                        <td>$ <?= number_format((float)$sale['total'], 2, ',', '.') ?></td>
                        <td>
                            <a
                                class="btn btn-sm <?= $selectedSaleId === (int)$sale['id'] ? 'btn-primary' : 'btn-outline-primary' ?>"
                                href="<?= BASE_URL ?>/sales/history?from=<?= urlencode($fromDate) ?>&to=<?= urlencode($toDate) ?>&sale_id=<?= (int)$sale['id'] ?>"
                            >Ver detalle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($selectedSaleId > 0): ?>
<div class="card">
    <div class="card-header">Detalle de venta #<?= (int)$selectedSaleId ?></div>
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Codigo</th>
                    <th>Cantidad</th>
                    <th>P. Unit</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($selectedSaleItems)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Sin items para mostrar.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($selectedSaleItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['barcode']) ?></td>
                        <td><?= number_format((float)$item['quantity'], 3, ',', '.') . ' ' . htmlspecialchars($item['unit_label']) ?></td>
                        <td>$ <?= number_format((float)$item['unit_price'], 2, ',', '.') ?></td>
                        <td>$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

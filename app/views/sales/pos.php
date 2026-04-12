<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Ventas Rapidas (POS)</h1>
    <span class="badge text-bg-primary p-2">Total: $ <?= number_format($total, 2, ',', '.') ?></span>
</div>

<div class="card mb-4">
    <div class="card-header">Agregar por Lector de Codigo</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/sales/add-item" class="row g-3" id="posForm">
            <div class="col-md-5">
                <label class="form-label">Codigo de barras</label>
                <input id="barcodeInput" name="barcode" class="form-control form-control-lg" autofocus required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cantidad</label>
                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control form-control-lg" value="1" required>
                <small class="text-muted">Para fiambres ingresar gramos. Ej: 250</small>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success btn-lg w-100">Agregar</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Buscador Manual (nombre o codigo)</div>
    <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/sales/pos" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label">Buscar producto</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Ej: oreo o 7622201735906">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary w-100">Buscar</button>
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/sales/pos">Limpiar</a>
            </div>
        </form>

        <?php if (!empty($searchTerm)): ?>
            <hr>
            <h2 class="h6">Resultados</h2>
            <?php if (empty($searchResults)): ?>
                <p class="text-muted mb-0">No se encontraron productos para "<?= htmlspecialchars($searchTerm) ?>".</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Codigo</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th style="width: 220px;">Agregar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['barcode']) ?></td>
                                    <td>$ <?= number_format((float)$p['sale_price'], 2, ',', '.') ?></td>
                                    <td>
                                        <?= $p['unit_type'] === 'weight'
                                            ? number_format((float)$p['stock_kg'], 3, ',', '.') . ' kg'
                                            : number_format((float)$p['stock_units'], 0, ',', '.') . ' un.' ?>
                                    </td>
                                    <td>
                                        <form method="post" action="<?= BASE_URL ?>/sales/add-item" class="d-flex gap-2">
                                            <input type="hidden" name="barcode" value="<?= htmlspecialchars($p['barcode']) ?>">
                                            <input
                                                type="number"
                                                step="0.001"
                                                min="0.001"
                                                name="quantity"
                                                class="form-control form-control-sm"
                                                value="<?= $p['unit_type'] === 'weight' ? '100' : '1' ?>"
                                            >
                                            <button class="btn btn-sm btn-success">Agregar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Carrito Actual</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr><th>Producto</th><th>Cantidad</th><th>P. Unit</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($cart as $index => $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format((float)$item['quantity'], 3, ',', '.') . ' ' . $item['unit_label'] ?></td>
                    <td>$ <?= number_format((float)$item['unit_price'], 2, ',', '.') ?></td>
                    <td>$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/sales/remove-item">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button class="btn btn-sm btn-outline-danger">Quitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/sales/checkout" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Medio de Pago</label>
                <select name="payment_method" class="form-select">
                    <option value="efectivo">Efectivo</option>
                    <option value="debito">Debito</option>
                    <option value="credito">Credito</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="fs-4">Total Final: <strong>$ <?= number_format($total, 2, ',', '.') ?></strong></div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary btn-lg w-100" <?= empty($cart) ? 'disabled' : '' ?>>Cobrar Venta</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

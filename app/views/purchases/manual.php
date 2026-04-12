<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Ingreso Manual de Mercaderia</h1>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/purchases/invoices">Cargar Factura/Ticket</a>
</div>

<div class="card mb-4">
    <div class="card-header">Nuevo ingreso</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/purchases/manual-store" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Producto</label>
                <select name="product_id" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int)$product['id'] ?>">
                            <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['barcode']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Costo unitario</label>
                <input type="text" name="unit_cost" class="form-control" placeholder="Ej: 2500 o 2.500">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Sin proveedor</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int)$supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nro. comprobante</label>
                <input type="text" name="doc_number" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-md-8">
                <label class="form-label">Observaciones</label>
                <input type="text" name="notes" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Registrar ingreso manual</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Ultimos movimientos de ingreso</div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Proveedor</th>
                    <th>Comprobante</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Sin movimientos cargados.</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $row): ?>
                    <tr>
                        <td><?= (int)$row['id'] ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars(strtoupper($row['doc_type'])) ?></td>
                        <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['doc_number'] ?? '-') ?></td>
                        <td>$ <?= number_format((float)$row['total'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

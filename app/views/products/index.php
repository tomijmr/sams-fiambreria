<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Productos y Stock</h1>
</div>

<div class="card mb-4">
    <div class="card-header">Nuevo Producto</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/products/store" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Codigo de Barras</label>
                <input name="barcode" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="unit_type" class="form-select">
                    <option value="unit">Unidad</option>
                    <option value="weight">Peso</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Sin proveedor</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock (kg)</label>
                <input type="number" step="0.001" name="stock_kg" class="form-control" value="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock (un.)</label>
                <input type="number" name="stock_units" class="form-control" value="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Costo</label>
                <input type="number" step="0.01" name="cost_price" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">% Ganancia</label>
                <input type="number" step="0.01" name="profit_percent" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Listado</div>
    <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Barcode</th>
                    <th>Tipo</th>
                    <th>Stock</th>
                    <th>Costo</th>
                    <th>%</th>
                    <th>P. Venta</th>
                    <th>Proveedor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['barcode']) ?></td>
                        <td><?= $p['unit_type'] === 'weight' ? 'Peso' : 'Unidad' ?></td>
                        <td><?= $p['unit_type'] === 'weight' ? number_format((float)$p['stock_kg'], 3, ',', '.') . ' kg' : (int)$p['stock_units'] ?></td>
                        <td>$ <?= number_format((float)$p['cost_price'], 2, ',', '.') ?></td>
                        <td><?= number_format((float)$p['profit_percent'], 2, ',', '.') ?></td>
                        <td>$ <?= number_format((float)$p['sale_price'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['supplier_name'] ?? '-') ?></td>
                        <td>
                            <form method="post" action="<?= BASE_URL ?>/products/delete" onsubmit="return confirm('Eliminar producto?')">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

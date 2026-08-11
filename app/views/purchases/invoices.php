<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Facturas y Tickets de Proveedores</h1>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/purchases/manual">Ingreso Manual</a>
</div>

<div class="card mb-4">
    <div class="card-header">Cargar comprobante</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/purchases/invoices/add-item" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int)$supplier['id'] ?>" <?= (int)$header['supplier_id'] === (int)$supplier['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($supplier['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="doc_type" class="form-select">
                    <option value="invoice" <?= $header['doc_type'] === 'invoice' ? 'selected' : '' ?>>Factura</option>
                    <option value="ticket" <?= $header['doc_type'] === 'ticket' ? 'selected' : '' ?>>Ticket</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nro. comprobante</label>
                <input type="text" name="doc_number" class="form-control" value="<?= htmlspecialchars($header['doc_number']) ?>" placeholder="Ej: 0001-00012345">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" value="<?= htmlspecialchars($header['date']) ?>" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Observacion</label>
                <input type="text" name="notes" class="form-control" value="<?= htmlspecialchars($header['notes']) ?>" placeholder="Opcional">
            </div>

            <div class="col-12"><hr class="my-1"></div>

            <div class="col-md-3">
                <label class="form-label">Codigo de barras</label>
                <input type="text" name="barcode" class="form-control" required autofocus placeholder="Escanea o tipea el codigo">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombre <span class="text-muted">(solo si es producto nuevo)</span></label>
                <input type="text" name="name" class="form-control" placeholder="Ej: Jamon Cocido">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo <span class="text-muted">(si es nuevo)</span></label>
                <select name="unit_type" class="form-select">
                    <option value="unit">Unidad</option>
                    <option value="weight">Peso (kg)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Costo unitario</label>
                <input type="number" step="0.01" min="0.01" name="unit_cost" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ganancia % <span class="text-muted">(si es nuevo)</span></label>
                <input type="number" step="0.1" name="profit_percent" class="form-control" value="30">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success w-100">Agregar item</button>
            </div>

            <div class="col-12">
                <small class="text-muted">
                    Si el codigo de barras ya existe, se usa el producto cargado y se ignoran los campos "solo si es nuevo".
                    Si no existe, se crea el producto con el nombre, tipo y ganancia indicados.
                </small>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Items del comprobante actual <?= !empty($items) ? '(' . count($items) . ')' : '' ?></div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo Unit.</th>
                    <th>Subtotal</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">Todavia no agregaste items a este comprobante.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['barcode']) ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>
                            <?= number_format((float)$item['quantity'], 3, ',', '.') ?>
                            <?= $item['unit_type'] === 'weight' ? 'kg' : 'un.' ?>
                        </td>
                        <td>$ <?= number_format((float)$item['unit_cost'], 2, ',', '.') ?></td>
                        <td>$ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
                        <td><?= !empty($item['is_new']) ? '<span class="badge text-bg-success">Nuevo</span>' : '' ?></td>
                        <td>
                            <form method="post" action="<?= BASE_URL ?>/purchases/invoices/remove-item">
                                <?= csrf_field() ?>
                                <input type="hidden" name="index" value="<?= $index ?>">
                                <button class="btn btn-sm btn-outline-danger">Quitar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if (!empty($items)): ?>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th colspan="3">$ <?= number_format($draftTotal, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/purchases/invoices/checkout" onsubmit="return confirm('Confirmar la compra? Se va a actualizar el stock y los costos.')">
            <?= csrf_field() ?>
            <button class="btn btn-primary" <?= empty($items) ? 'disabled' : '' ?>>Confirmar compra y actualizar stock</button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Ultimas Facturas</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>ID</th><th>Fecha</th><th>Proveedor</th><th>Nro</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentInvoices)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin facturas registradas.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($recentInvoices as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['doc_number'] ?? '-') ?></td>
                                <td>$ <?= number_format((float)$row['total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Ultimos Tickets</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>ID</th><th>Fecha</th><th>Proveedor</th><th>Nro</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentTickets)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Sin tickets registrados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($recentTickets as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['supplier_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['doc_number'] ?? '-') ?></td>
                                <td>$ <?= number_format((float)$row['total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

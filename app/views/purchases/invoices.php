<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Facturas y Tickets de Proveedores</h1>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/purchases/manual">Ingreso Manual</a>
</div>

<div class="card mb-4">
    <div class="card-header">Registrar comprobante de compra</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/purchases/invoices-store" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Proveedor</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= (int)$supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="doc_type" class="form-select">
                    <option value="invoice">Factura</option>
                    <option value="ticket">Ticket</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Nro. comprobante</label>
                <input type="text" name="doc_number" class="form-control" placeholder="Ej: 0001-00012345">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Observacion</label>
                <input type="text" name="notes" class="form-control" placeholder="Opcional">
            </div>

            <div class="col-12">
                <label class="form-label">Items (uno por linea: barcode,cantidad,costo_unitario)</label>
                <textarea name="items_text" rows="8" class="form-control" placeholder="7622201735906,12,1900&#10;7792540260138,6,1200" required></textarea>
                <div class="form-text">Para productos por peso, la cantidad es en kilogramos. Ejemplo: 3.5</div>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">Registrar factura/ticket y actualizar stock + costos</button>
            </div>
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

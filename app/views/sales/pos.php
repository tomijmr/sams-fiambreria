<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Ventas Rapidas (POS)</h1>
    <span class="badge text-bg-primary p-2">Total: $ <?= number_format($total, 2, ',', '.') ?></span>
</div>

<div class="card mb-4">
    <div class="card-header">Agregar por Lector de Codigo</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/sales/add-item" class="row g-3" id="posForm">
            <?= csrf_field() ?>
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
    <div class="card-header">Buscar por Nombre o Codigo</div>
    <div class="card-body">
        <form method="get" action="<?= BASE_URL ?>/sales/pos" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label">Producto</label>
                <input type="text" name="q" class="form-control form-control-lg" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Escribi el nombre o el codigo. Ej: jamon o 7622201735906" autocomplete="off">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary w-100">Buscar</button>
                <?php if (!empty($searchTerm)): ?>
                    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/sales/pos">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!empty($searchTerm)): ?>
            <hr>
            <?php if (empty($searchResults)): ?>
                <p class="text-muted mb-0">No se encontraron productos para "<?= htmlspecialchars($searchTerm) ?>".</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($searchResults as $p): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($p['barcode']) ?>
                                    &middot; $ <?= number_format((float)$p['sale_price'], 2, ',', '.') ?>
                                    &middot; Stock:
                                    <?= $p['unit_type'] === 'weight'
                                        ? number_format((float)$p['stock_kg'], 3, ',', '.') . ' kg'
                                        : number_format((float)$p['stock_units'], 0, ',', '.') . ' un.' ?>
                                </div>
                            </div>
                            <?php if ($p['unit_type'] === 'weight'): ?>
                                <form method="post" action="<?= BASE_URL ?>/sales/add-item" class="d-flex gap-2 align-items-center">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="barcode" value="<?= htmlspecialchars($p['barcode']) ?>">
                                    <input
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        name="quantity"
                                        class="form-control form-control-sm"
                                        style="width: 100px;"
                                        value="100"
                                        title="Gramos"
                                    >
                                    <button class="btn btn-success">Agregar</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= BASE_URL ?>/sales/add-item">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="barcode" value="<?= htmlspecialchars($p['barcode']) ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="btn btn-success">Agregar al carrito</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
                            <?= csrf_field() ?>
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
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="fs-4 mb-0">Total Final: <strong>$ <?= number_format($total, 2, ',', '.') ?></strong></div>
                <small class="text-muted">Confirma el cobro desde la ventana emergente.</small>
            </div>
            <div class="col-md-4">
                <button
                    type="button"
                    class="btn btn-primary btn-lg w-100"
                    data-bs-toggle="modal"
                    data-bs-target="#checkoutModal"
                    <?= empty($cart) ? 'disabled' : '' ?>
                >
                    Cobrar Venta
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/sales/checkout">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-primary py-2">
                        Total a cobrar: <strong>$ <span id="modalTotalLabel"><?= number_format($total, 2, ',', '.') ?></span></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Medio de Pago</label>
                        <select name="payment_method" id="paymentMethod" class="form-select">
                            <option value="efectivo">Efectivo</option>
                            <option value="debito">Debito</option>
                            <option value="credito">Credito</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="mb-3" id="cashReceivedGroup">
                        <label class="form-label">Cliente paga con</label>
                        <input type="text" inputmode="decimal" name="amount_paid" id="amountPaid" class="form-control" placeholder="0,00" autocomplete="off">
                    </div>

                    <div id="changeSummary" class="alert alert-light border mb-0 py-2">
                        Vuelto: <strong id="changeAmount">$ 0,00</strong>
                        <span class="text-danger ms-2" id="changeWarning"></span>
                    </div>

                    <input type="hidden" id="posTotal" value="<?= number_format((float)$total, 2, '.', '') ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Confirmar y Cobrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

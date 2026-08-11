<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Proveedores</h1>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/purchases/invoices">Cargar Factura/Ticket</a>
</div>

<div class="card mb-4">
    <div class="card-header">Nuevo Proveedor</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/suppliers/store" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-4"><input name="name" class="form-control" placeholder="Nombre" required></div>
            <div class="col-md-3"><input name="phone" class="form-control" placeholder="Telefono"></div>
            <div class="col-md-3"><input name="contact" class="form-control" placeholder="Contacto"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Guardar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Nombre</th><th>Telefono</th><th>Contacto</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($suppliers as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['phone']) ?></td>
                    <td><?= htmlspecialchars($s['contact']) ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/suppliers/delete" onsubmit="return confirm('Eliminar proveedor?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

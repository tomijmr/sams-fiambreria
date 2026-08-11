<?php require __DIR__ . '/../layouts/header.php'; ?>
<h1 class="h3 mb-3">Gastos</h1>

<div class="card mb-4">
    <div class="card-header">Nuevo Gasto</div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/expenses/store" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-4"><input name="description" class="form-control" placeholder="Descripcion" required></div>
            <div class="col-md-3"><input name="category" class="form-control" placeholder="Categoria"></div>
            <div class="col-md-2"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Monto" required></div>
            <div class="col-md-2"><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">OK</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Fecha</th><th>Descripcion</th><th>Categoria</th><th>Monto</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['date']) ?></td>
                    <td><?= htmlspecialchars($e['description']) ?></td>
                    <td><?= htmlspecialchars($e['category']) ?></td>
                    <td>$ <?= number_format((float)$e['amount'], 2, ',', '.') ?></td>
                    <td>
                        <form method="post" action="<?= BASE_URL ?>/expenses/delete" onsubmit="return confirm('Eliminar gasto?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $e['id'] ?>">
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

<?php

class SupplierController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('suppliers/index', ['suppliers' => (new Supplier())->all()]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        csrf_verify();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'contact' => trim($_POST['contact'] ?? ''),
        ];
        (new Supplier())->create($data);
        Audit::record('create', 'supplier', (int)Database::getInstance()->lastInsertId(), 'Proveedor creado: ' . $data['name']);
        $this->redirect('/suppliers');
    }

    public function update(): void
    {
        Auth::requireAdmin();
        csrf_verify();
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'contact' => trim($_POST['contact'] ?? ''),
        ];
        (new Supplier())->update($id, $data);
        Audit::record('update', 'supplier', $id, 'Proveedor actualizado: ' . $data['name']);
        $this->redirect('/suppliers');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        csrf_verify();
        $id = (int)($_POST['id'] ?? 0);
        (new Supplier())->delete($id);
        Audit::record('delete', 'supplier', $id, 'Proveedor eliminado');
        $this->redirect('/suppliers');
    }
}

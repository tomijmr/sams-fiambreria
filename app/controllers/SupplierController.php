<?php

class SupplierController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('suppliers/index', ['suppliers' => (new Supplier())->all()]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        (new Supplier())->create([
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'contact' => trim($_POST['contact'] ?? ''),
        ]);
        $this->redirect('/suppliers');
    }

    public function update(): void
    {
        Auth::requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        (new Supplier())->update($id, [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'contact' => trim($_POST['contact'] ?? ''),
        ]);
        $this->redirect('/suppliers');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        (new Supplier())->delete($id);
        $this->redirect('/suppliers');
    }
}

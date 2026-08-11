<?php

class ExpenseController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('expenses/index', ['expenses' => (new Expense())->all()]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        csrf_verify();

        $data = [
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? 'General'),
            'amount' => (float)($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
        ];
        (new Expense())->create($data);
        Audit::record('create', 'expense', (int)Database::getInstance()->lastInsertId(), 'Gasto creado: ' . $data['description'] . ' ($ ' . $data['amount'] . ')');

        $this->redirect('/expenses');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        csrf_verify();
        $id = (int)($_POST['id'] ?? 0);
        (new Expense())->delete($id);
        Audit::record('delete', 'expense', $id, 'Gasto eliminado');
        $this->redirect('/expenses');
    }
}

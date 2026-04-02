<?php

class ExpenseController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $this->view('expenses/index', ['expenses' => (new Expense())->all()]);
    }

    public function store(): void
    {
        Auth::requireLogin();

        (new Expense())->create([
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? 'General'),
            'amount' => (float)($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
        ]);

        $this->redirect('/expenses');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        (new Expense())->delete((int)($_POST['id'] ?? 0));
        $this->redirect('/expenses');
    }
}

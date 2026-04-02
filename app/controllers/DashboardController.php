<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $saleModel = new Sale();
        $expenseModel = new Expense();
        $reportModel = new Report();

        $data = [
            'salesToday' => $saleModel->totalToday(),
            'ticketsToday' => $saleModel->countToday(),
            'expensesMonth' => $expenseModel->totalMonth(),
            'lowStock' => $reportModel->lowStock(),
        ];

        $this->view('dashboard/index', $data);
    }
}

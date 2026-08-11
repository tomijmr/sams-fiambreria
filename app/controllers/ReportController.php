<?php

class ReportController extends Controller
{
    public function dailyCash(): void
    {
        Auth::requireAdmin();

        $date = $_GET['date'] ?? date('Y-m-d');
        if (!$this->isValidDate($date)) {
            $date = date('Y-m-d');
        }

        $saleModel = new Sale();
        $expenseModel = new Expense();

        $sales = $saleModel->byDate($date);
        $paymentSummary = $saleModel->paymentMethodSummaryByDate($date);
        $incomeTotal = $saleModel->totalByDate($date);

        $expenses = $expenseModel->byDate($date);
        $expenseTotal = $expenseModel->totalByDate($date);
        $providerPayments = $expenseModel->providerPaymentsByDate($date);
        $otherExpenses = max(0, $expenseTotal - $providerPayments);

        $this->view('reports/daily_cash', [
            'date' => $date,
            'sales' => $sales,
            'paymentSummary' => $paymentSummary,
            'expenses' => $expenses,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'providerPayments' => $providerPayments,
            'otherExpenses' => $otherExpenses,
            'netTotal' => $incomeTotal - $expenseTotal,
        ]);
    }

    public function activityLog(): void
    {
        Auth::requireAdmin();

        $entries = Database::getInstance()
            ->query('SELECT * FROM activity_log ORDER BY created_at DESC, id DESC LIMIT 200')
            ->fetchAll();

        $this->view('reports/activity_log', ['entries' => $entries]);
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

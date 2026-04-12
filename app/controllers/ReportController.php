<?php

class ReportController extends Controller
{
    public function dailyCash(): void
    {
        Auth::requireLogin();

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

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

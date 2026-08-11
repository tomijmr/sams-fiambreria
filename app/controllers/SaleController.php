<?php

class SaleController extends Controller
{
    public function history(): void
    {
        Auth::requireLogin();

        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate = $_GET['to'] ?? date('Y-m-d');

        if (!$this->isValidDate($fromDate)) {
            $fromDate = date('Y-m-01');
        }
        if (!$this->isValidDate($toDate)) {
            $toDate = date('Y-m-d');
        }

        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $saleModel = new Sale();
        $sales = $saleModel->historyByDateRange($fromDate, $toDate);

        $selectedSaleId = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;
        $selectedSaleItems = [];
        if ($selectedSaleId > 0) {
            $selectedSaleItems = $saleModel->itemsBySale($selectedSaleId);
        }

        $totalRange = (float)array_reduce($sales, function ($carry, $sale) {
            return $carry + (float)$sale['total'];
        }, 0);

        $this->view('sales/history', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'sales' => $sales,
            'totalRange' => $totalRange,
            'selectedSaleId' => $selectedSaleId,
            'selectedSaleItems' => $selectedSaleItems,
        ]);
    }

    public function pos(): void
    {
        Auth::requireLogin();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $searchTerm = trim($_GET['q'] ?? '');
        $searchResults = [];
        if ($searchTerm !== '') {
            $searchResults = (new Product())->searchForPos($searchTerm);
        }

        $this->view('sales/pos', [
            'cart' => $_SESSION['cart'],
            'total' => $this->cartTotal($_SESSION['cart']),
            'searchTerm' => $searchTerm,
            'searchResults' => $searchResults,
        ]);
    }

    public function addItem(): void
    {
        Auth::requireLogin();
        csrf_verify();

        $barcode = trim($_POST['barcode'] ?? '');
        $quantity = (float)($_POST['quantity'] ?? 1);
        $product = (new Product())->findByBarcode($barcode);

        if (!$product) {
            $_SESSION['error'] = 'Producto no encontrado por codigo de barras';
            $this->redirect('/sales/pos');
        }

        $unitLabel = $product['unit_type'] === 'weight' ? 'gramos' : 'unidades';
        $unitPrice = (float)$product['sale_price'];

        if ($product['unit_type'] === 'weight') {
            $subtotal = ($unitPrice / 1000) * $quantity;
        } else {
            $subtotal = $unitPrice * $quantity;
        }

        $_SESSION['cart'][] = [
            'product_id' => (int)$product['id'],
            'name' => $product['name'],
            'barcode' => $product['barcode'],
            'unit_type' => $product['unit_type'],
            'quantity' => $quantity,
            'unit_label' => $unitLabel,
            'unit_price' => $unitPrice,
            'subtotal' => round($subtotal, 2),
        ];

        $this->redirect('/sales/pos');
    }

    public function removeItem(): void
    {
        Auth::requireLogin();
        csrf_verify();

        $index = (int)($_POST['index'] ?? -1);
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        $this->redirect('/sales/pos');
    }

    public function checkout(): void
    {
        Auth::requireLogin();
        csrf_verify();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $_SESSION['error'] = 'No hay items para cobrar';
            $this->redirect('/sales/pos');
        }

        $total = $this->cartTotal($cart);
        $paymentMethod = $_POST['payment_method'] ?? 'efectivo';
        $amountPaid = $this->parseAmount($_POST['amount_paid'] ?? null);

        if ($paymentMethod === 'efectivo' && $amountPaid < $total) {
            $faltante = $total - $amountPaid;
            $_SESSION['error'] = 'El monto recibido es menor al total. Faltan $ ' . number_format($faltante, 2, ',', '.');
            $this->redirect('/sales/pos');
        }

        $change = $paymentMethod === 'efectivo' ? max(0, $amountPaid - $total) : 0;

        (new Sale())->createSale([
            'total' => $total,
            'payment_method' => $paymentMethod,
        ], $cart);

        Audit::record('create', 'sale', null, 'Venta por $ ' . number_format($total, 2, '.', '') . ' (' . $paymentMethod . ')');

        $_SESSION['cart'] = [];
        $_SESSION['success'] = $paymentMethod === 'efectivo'
            ? 'Venta registrada correctamente. Vuelto: $ ' . number_format($change, 2, ',', '.')
            : 'Venta registrada correctamente';
        $this->redirect('/sales/pos');
    }

    private function cartTotal(array $cart): float
    {
        return (float)array_reduce($cart, function ($carry, $item) {
            return $carry + (float)$item['subtotal'];
        }, 0);
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function parseAmount($value): float
    {
        if ($value === null) {
            return 0;
        }

        $normalized = trim((string)$value);
        if ($normalized === '') {
            return 0;
        }

        $normalized = str_replace(['$', ' '], '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return max(0, (float)$normalized);
    }
}

<?php

class PurchaseController extends Controller
{
    public function manual(): void
    {
        Auth::requireAdmin();

        $this->view('purchases/manual', [
            'products' => (new Product())->all(),
            'suppliers' => (new Supplier())->all(),
            'recent' => (new Purchase())->recent(15),
        ]);
    }

    public function manualStore(): void
    {
        Auth::requireAdmin();
        csrf_verify();

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = $this->parseNumber($_POST['quantity'] ?? '0');
        $unitCost = $this->parseMoney($_POST['unit_cost'] ?? '0');
        $supplierId = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $date = $_POST['date'] ?? date('Y-m-d');

        if ($productId <= 0 || $quantity <= 0) {
            $_SESSION['error'] = 'Completa producto y cantidad para registrar el ingreso.';
            $this->redirect('/purchases/manual');
        }

        try {
            (new Purchase())->create([
                'supplier_id' => $supplierId,
                'doc_type' => 'manual',
                'doc_number' => trim($_POST['doc_number'] ?? ''),
                'date' => $date,
                'notes' => trim($_POST['notes'] ?? ''),
            ], [[
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
            ]]);

            Audit::record('create', 'purchase', null, 'Ingreso manual: producto #' . $productId . ' x' . $quantity);
            $_SESSION['success'] = 'Ingreso manual registrado y stock actualizado.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'No se pudo registrar el ingreso manual.';
        }

        $this->redirect('/purchases/manual');
    }

    public function invoices(): void
    {
        Auth::requireAdmin();

        $draft = $this->draft();

        $this->view('purchases/invoices', [
            'suppliers' => (new Supplier())->all(),
            'recentInvoices' => (new Purchase())->recent(20, 'invoice'),
            'recentTickets' => (new Purchase())->recent(20, 'ticket'),
            'header' => $draft['header'],
            'items' => $draft['items'],
            'draftTotal' => array_reduce($draft['items'], fn ($carry, $item) => $carry + (float)$item['subtotal'], 0.0),
        ]);
    }

    public function invoiceAddItem(): void
    {
        Auth::requireAdmin();
        csrf_verify();

        $_SESSION['purchase_draft']['header'] = [
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'doc_type' => ($_POST['doc_type'] ?? 'invoice') === 'ticket' ? 'ticket' : 'invoice',
            'doc_number' => trim($_POST['doc_number'] ?? ''),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $barcode = trim($_POST['barcode'] ?? '');
        $quantity = $this->parseNumber($_POST['quantity'] ?? '0');
        $unitCost = $this->parseMoney($_POST['unit_cost'] ?? '0');

        if ($barcode === '' || $quantity <= 0 || $unitCost <= 0) {
            $_SESSION['error'] = 'Completa codigo de barras, cantidad y costo unitario validos.';
            $this->redirect('/purchases/invoices');
        }

        $productModel = new Product();
        $product = $productModel->findByBarcode($barcode);
        $isNew = false;

        if (!$product) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $_SESSION['error'] = 'El codigo "' . $barcode . '" no existe todavia. Ingresa el nombre para crear el producto nuevo.';
                $this->redirect('/purchases/invoices');
            }

            $unitType = ($_POST['unit_type'] ?? 'unit') === 'weight' ? 'weight' : 'unit';
            $profitPercent = (float)($_POST['profit_percent'] ?? 30);
            $supplierId = !empty($_SESSION['purchase_draft']['header']['supplier_id'])
                ? (int)$_SESSION['purchase_draft']['header']['supplier_id']
                : null;

            $productModel->create([
                'name' => $name,
                'barcode' => $barcode,
                'unit_type' => $unitType,
                'stock_kg' => 0,
                'stock_units' => 0,
                'cost_price' => 0,
                'profit_percent' => $profitPercent,
                'sale_price' => 0,
                'supplier_id' => $supplierId,
            ]);

            $newId = (int)Database::getInstance()->lastInsertId();
            $product = $productModel->find($newId);
            $isNew = true;
            Audit::record('create', 'product', $newId, 'Producto creado desde carga de compra: ' . $name);
        }

        $_SESSION['purchase_draft']['items'][] = [
            'product_id' => (int)$product['id'],
            'barcode' => $product['barcode'],
            'name' => $product['name'],
            'unit_type' => $product['unit_type'],
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'subtotal' => round($quantity * $unitCost, 2),
            'is_new' => $isNew,
        ];

        $this->redirect('/purchases/invoices');
    }

    public function invoiceRemoveItem(): void
    {
        Auth::requireAdmin();
        csrf_verify();

        $index = (int)($_POST['index'] ?? -1);
        if (isset($_SESSION['purchase_draft']['items'][$index])) {
            unset($_SESSION['purchase_draft']['items'][$index]);
            $_SESSION['purchase_draft']['items'] = array_values($_SESSION['purchase_draft']['items']);
        }

        $this->redirect('/purchases/invoices');
    }

    public function invoiceCheckout(): void
    {
        Auth::requireAdmin();
        csrf_verify();

        $draft = $this->draft();
        $header = $draft['header'];
        $items = $draft['items'];
        $supplierId = (int)$header['supplier_id'];

        if ($supplierId <= 0 || empty($items)) {
            $_SESSION['error'] = 'Selecciona proveedor y agrega al menos un item antes de confirmar.';
            $this->redirect('/purchases/invoices');
        }

        try {
            $purchaseId = (new Purchase())->create([
                'supplier_id' => $supplierId,
                'doc_type' => $header['doc_type'],
                'doc_number' => $header['doc_number'],
                'date' => $header['date'],
                'notes' => $header['notes'],
            ], array_map(fn ($item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
            ], $items));

            Audit::record('create', 'purchase', $purchaseId, 'Factura/ticket de proveedor #' . $supplierId . ' con ' . count($items) . ' items');
            unset($_SESSION['purchase_draft']);
            $_SESSION['success'] = 'Compra registrada. Stock y costos actualizados.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'No se pudo registrar la compra.';
        }

        $this->redirect('/purchases/invoices');
    }

    private function draft(): array
    {
        if (!isset($_SESSION['purchase_draft'])) {
            $_SESSION['purchase_draft'] = [
                'header' => [
                    'supplier_id' => 0,
                    'doc_type' => 'invoice',
                    'doc_number' => '',
                    'date' => date('Y-m-d'),
                    'notes' => '',
                ],
                'items' => [],
            ];
        }

        return $_SESSION['purchase_draft'];
    }

    private function parseMoney(string $value): float
    {
        $normalized = str_replace(['$', ' '], '', trim($value));
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);
        return (float)$normalized;
    }

    private function parseNumber(string $value): float
    {
        $normalized = str_replace(' ', '', trim($value));
        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return (float)$normalized;
    }
}

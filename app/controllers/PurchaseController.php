<?php

class PurchaseController extends Controller
{
    public function manual(): void
    {
        Auth::requireLogin();

        $this->view('purchases/manual', [
            'products' => (new Product())->all(),
            'suppliers' => (new Supplier())->all(),
            'recent' => (new Purchase())->recent(15),
        ]);
    }

    public function manualStore(): void
    {
        Auth::requireLogin();

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

            $_SESSION['success'] = 'Ingreso manual registrado y stock actualizado.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'No se pudo registrar el ingreso manual.';
        }

        $this->redirect('/purchases/manual');
    }

    public function invoices(): void
    {
        Auth::requireLogin();

        $this->view('purchases/invoices', [
            'suppliers' => (new Supplier())->all(),
            'recentInvoices' => (new Purchase())->recent(20, 'invoice'),
            'recentTickets' => (new Purchase())->recent(20, 'ticket'),
        ]);
    }

    public function invoiceStore(): void
    {
        Auth::requireLogin();

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $docType = $_POST['doc_type'] ?? 'invoice';
        $docNumber = trim($_POST['doc_number'] ?? '');
        $date = $_POST['date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');
        $itemsText = trim($_POST['items_text'] ?? '');

        if ($supplierId <= 0 || $itemsText === '') {
            $_SESSION['error'] = 'Selecciona proveedor y carga items para registrar factura/ticket.';
            $this->redirect('/purchases/invoices');
        }

        $lines = preg_split('/\r\n|\r|\n/', $itemsText);
        $items = [];
        $errors = [];
        $productModel = new Product();

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = preg_split('/[;,\t]+/', $line);
            if (count($parts) < 3) {
                $errors[] = 'Linea ' . ($index + 1) . ': formato invalido.';
                continue;
            }

            $barcode = trim($parts[0]);
            $quantity = $this->parseNumber($parts[1]);
            $unitCost = $this->parseMoney($parts[2]);

            if ($barcode === '' || $quantity <= 0 || $unitCost <= 0) {
                $errors[] = 'Linea ' . ($index + 1) . ': codigo/cantidad/costo invalidos.';
                continue;
            }

            $product = $productModel->findByBarcode($barcode);
            if (!$product) {
                $errors[] = 'Linea ' . ($index + 1) . ': barcode no encontrado (' . $barcode . ').';
                continue;
            }

            $items[] = [
                'product_id' => (int)$product['id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
            ];
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            $this->redirect('/purchases/invoices');
        }

        try {
            (new Purchase())->create([
                'supplier_id' => $supplierId,
                'doc_type' => $docType === 'ticket' ? 'ticket' : 'invoice',
                'doc_number' => $docNumber,
                'date' => $date,
                'notes' => $notes,
            ], $items);

            $_SESSION['success'] = 'Factura/ticket registrado, costos y stock actualizados.';
        } catch (Throwable $e) {
            $_SESSION['error'] = 'No se pudo registrar la factura/ticket.';
        }

        $this->redirect('/purchases/invoices');
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

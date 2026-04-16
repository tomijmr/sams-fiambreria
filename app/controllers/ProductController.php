<?php

class ProductController extends Controller
{
    public function index(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);

        Auth::requireLogin();
        $productModel = new Product();
        $supplierModel = new Supplier();

        $this->view('products/index', [
            'products' => $productModel->all(),
            'suppliers' => $supplierModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->store();
    }

    public function store(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products');
        }

        (new Product())->create($this->buildProductDataFromRequest());
        $this->redirect('/products');
    }

    public function edit(): void
    {
        $this->update();
    }

    public function update(): void
    {
        Auth::requireLogin();
        $id = (int)($_POST['id'] ?? 0);

        (new Product())->update($id, $this->buildProductDataFromRequest());
        $this->redirect('/products');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        (new Product())->delete($id);
        $this->redirect('/products');
    }

    public function bulkUpload(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/products');
        }

        if (empty($_FILES['csv_file']) || ($_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'No se pudo subir el archivo CSV.';
            $this->redirect('/products');
        }

        $tmpName = $_FILES['csv_file']['tmp_name'];
        $delimiterInput = $_POST['delimiter'] ?? ',';
        $delimiter = $delimiterInput === 'tab' ? "\t" : $delimiterInput;
        $stockMode = ($_POST['stock_mode'] ?? 'replace') === 'add' ? 'add' : 'replace';
        $defaultUnitType = ($_POST['default_unit_type'] ?? 'unit') === 'weight' ? 'weight' : 'unit';
        $defaultProfit = (float)($_POST['default_profit_percent'] ?? 30);
        $defaultSupplierId = !empty($_POST['default_supplier_id']) ? (int)$_POST['default_supplier_id'] : null;

        $handle = fopen($tmpName, 'r');
        if (!$handle) {
            $_SESSION['error'] = 'No se pudo leer el archivo CSV.';
            $this->redirect('/products');
        }

        $firstRow = fgetcsv($handle, 0, $delimiter);
        if ($firstRow === false) {
            fclose($handle);
            $_SESSION['error'] = 'El CSV esta vacio.';
            $this->redirect('/products');
        }

        $productModel = new Product();
        $processed = 0;
        $skipped = 0;
        $errors = 0;

        $headerMap = $this->buildHeaderMap($firstRow);
        $hasHeader = $this->isHeaderRow($headerMap);

        if (!$hasHeader) {
            $this->processCsvRow(
                $firstRow,
                $productModel,
                $stockMode,
                $defaultUnitType,
                $defaultProfit,
                $defaultSupplierId,
                $processed,
                $skipped,
                $errors
            );
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $this->processCsvRow(
                $row,
                $productModel,
                $stockMode,
                $defaultUnitType,
                $defaultProfit,
                $defaultSupplierId,
                $processed,
                $skipped,
                $errors,
                $hasHeader ? $headerMap : null
            );
        }

        fclose($handle);

        $_SESSION['success'] = 'Importacion finalizada. Procesados: ' . $processed . ' | Omitidos: ' . $skipped . ' | Con error: ' . $errors;
        $this->redirect('/products');
    }

    private function processCsvRow(
        array $row,
        Product $productModel,
        string $stockMode,
        string $defaultUnitType,
        float $defaultProfit,
        ?int $defaultSupplierId,
        int &$processed,
        int &$skipped,
        int &$errors,
        ?array $headerMap = null
    ): void {
        $barcode = '';
        $name = '';
        $quantityRaw = '0';
        $priceRaw = '0';

        if ($headerMap !== null) {
            $barcode = $this->valueAt($row, $headerMap['codigo'] ?? null);
            $name = $this->valueAt($row, $headerMap['descripcion'] ?? null);
            $quantityRaw = $this->valueAt($row, $headerMap['cantidad'] ?? null);
            $priceRaw = $this->valueAt($row, $headerMap['precio'] ?? null);
        } else {
            $barcode = $this->valueAt($row, 0);
            $quantityRaw = $this->valueAt($row, 1);
            $name = $this->valueAt($row, 2);
            $priceRaw = $this->valueAt($row, 3);
        }

        $barcode = trim($barcode);
        $name = trim($name);
        $quantity = $this->parseNumber($quantityRaw);
        $salePrice = $this->parseMoney($priceRaw);

        if ($barcode === '' && $name === '') {
            $skipped++;
            return;
        }

        if ($barcode === '' || $quantity < 0) {
            $errors++;
            return;
        }

        try {
            $productModel->upsertFromBulk([
                'barcode' => $barcode,
                'name' => $name,
                'quantity' => $quantity,
                'sale_price' => $salePrice,
                'unit_type' => $defaultUnitType,
                'default_profit' => $defaultProfit,
                'supplier_id' => $defaultSupplierId,
            ], $stockMode);
            $processed++;
        } catch (Throwable $e) {
            $errors++;
        }
    }

    private function buildHeaderMap(array $row): array
    {
        $map = [];
        foreach ($row as $index => $column) {
            $key = strtolower(trim((string)$column));
            $key = str_replace(['_', '-', ' '], '', $key);

            if (in_array($key, ['codigo', 'barcode', 'codigobarras', 'cod'], true)) {
                $map['codigo'] = $index;
            }
            if (in_array($key, ['descripcion', 'descripcionproducto', 'nombre', 'producto'], true)) {
                $map['descripcion'] = $index;
            }
            if (in_array($key, ['cantidad', 'stock'], true)) {
                $map['cantidad'] = $index;
            }
            if (in_array($key, ['precio', 'precioventa', 'venta'], true)) {
                $map['precio'] = $index;
            }
        }

        return $map;
    }

    private function isHeaderRow(array $headerMap): bool
    {
        return isset($headerMap['codigo']) || isset($headerMap['descripcion']) || isset($headerMap['precio']);
    }

    private function valueAt(array $row, ?int $index): string
    {
        if ($index === null || !isset($row[$index])) {
            return '';
        }

        return (string)$row[$index];
    }

    private function parseMoney(string $value): float
    {
        $normalized = str_replace(['$', ' '], '', trim($value));
        if ($normalized === '' || !preg_match('/[0-9]/', $normalized)) {
            return 0;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return (float)$normalized;
    }

    private function parseNumber(string $value): float
    {
        $normalized = str_replace(' ', '', trim($value));
        if ($normalized === '') {
            return 0;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return (float)$normalized;
    }

    private function buildProductDataFromRequest(): array
    {
        $cost = max(0, (float)($_POST['cost_price'] ?? 0));
        $salePrice = max(0, (float)($_POST['sale_price'] ?? 0));
        $profit = $cost > 0 ? (($salePrice - $cost) / $cost) * 100 : 0;

        return [
            'name' => trim($_POST['name'] ?? ''),
            'barcode' => trim($_POST['barcode'] ?? ''),
            'unit_type' => $_POST['unit_type'] ?? 'unit',
            'stock_kg' => (float)($_POST['stock_kg'] ?? 0),
            'stock_units' => (int)($_POST['stock_units'] ?? 0),
            'cost_price' => round($cost, 2),
            'profit_percent' => round($profit, 2),
            'sale_price' => round($salePrice, 2),
            'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
        ];
    }
}

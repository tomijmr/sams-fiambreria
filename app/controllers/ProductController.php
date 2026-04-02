<?php

class ProductController extends Controller
{
    public function index(): void
    {
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

        $cost = (float)($_POST['cost_price'] ?? 0);
        $profit = (float)($_POST['profit_percent'] ?? 0);
        $salePrice = $cost + ($cost * $profit / 100);

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'barcode' => trim($_POST['barcode'] ?? ''),
            'unit_type' => $_POST['unit_type'] ?? 'unit',
            'stock_kg' => (float)($_POST['stock_kg'] ?? 0),
            'stock_units' => (int)($_POST['stock_units'] ?? 0),
            'cost_price' => $cost,
            'profit_percent' => $profit,
            'sale_price' => round($salePrice, 2),
            'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
        ];

        (new Product())->create($data);
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

        $cost = (float)($_POST['cost_price'] ?? 0);
        $profit = (float)($_POST['profit_percent'] ?? 0);
        $salePrice = $cost + ($cost * $profit / 100);

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'barcode' => trim($_POST['barcode'] ?? ''),
            'unit_type' => $_POST['unit_type'] ?? 'unit',
            'stock_kg' => (float)($_POST['stock_kg'] ?? 0),
            'stock_units' => (int)($_POST['stock_units'] ?? 0),
            'cost_price' => $cost,
            'profit_percent' => $profit,
            'sale_price' => round($salePrice, 2),
            'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
        ];

        (new Product())->update($id, $data);
        $this->redirect('/products');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        (new Product())->delete($id);
        $this->redirect('/products');
    }
}

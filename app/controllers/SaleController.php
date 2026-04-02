<?php

class SaleController extends Controller
{
    public function pos(): void
    {
        Auth::requireLogin();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $this->view('sales/pos', [
            'cart' => $_SESSION['cart'],
            'total' => $this->cartTotal($_SESSION['cart']),
        ]);
    }

    public function addItem(): void
    {
        Auth::requireLogin();

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

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $_SESSION['error'] = 'No hay items para cobrar';
            $this->redirect('/sales/pos');
        }

        $total = $this->cartTotal($cart);
        $paymentMethod = $_POST['payment_method'] ?? 'efectivo';

        (new Sale())->createSale([
            'total' => $total,
            'payment_method' => $paymentMethod,
        ], $cart);

        $_SESSION['cart'] = [];
        $_SESSION['success'] = 'Venta registrada correctamente';
        $this->redirect('/sales/pos');
    }

    private function cartTotal(array $cart): float
    {
        return (float)array_reduce($cart, function ($carry, $item) {
            return $carry + (float)$item['subtotal'];
        }, 0);
    }
}

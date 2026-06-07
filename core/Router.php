<?php

class Router
{
    public function dispatch(): void
    {
        // Opción 1: Si hay parámetro 'route' (fallback para hostings sin mod_rewrite)
        if (!empty($_GET['route'])) {
            $path = trim($_GET['route'], '/');
        } else {
            // Opción 2: Parsear la URI normalmente (con mod_rewrite)
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = str_replace(BASE_URL, '', $uri);
            $path = trim($path, '/');
        }

        if ($path === '') {
            $path = 'dashboard';
        }

        [$controllerName, $method] = $this->resolveRoute($path);

        if (!class_exists($controllerName)) {
            http_response_code(404);
            echo 'Controlador no encontrado';
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            echo 'Metodo no encontrado';
            return;
        }

        $controller->$method();
    }

    private function resolveRoute(string $path): array
    {
        $routes = [
            'login' => ['AuthController', 'login'],
            'logout' => ['AuthController', 'logout'],

            'dashboard' => ['DashboardController', 'index'],

            'products' => ['ProductController', 'index'],
            'products/create' => ['ProductController', 'create'],
            'products/store' => ['ProductController', 'store'],
            'products/edit' => ['ProductController', 'edit'],
            'products/update' => ['ProductController', 'update'],
            'products/delete' => ['ProductController', 'delete'],
            'products/bulk-upload' => ['ProductController', 'bulkUpload'],

            'suppliers' => ['SupplierController', 'index'],
            'suppliers/store' => ['SupplierController', 'store'],
            'suppliers/update' => ['SupplierController', 'update'],
            'suppliers/delete' => ['SupplierController', 'delete'],

            'expenses' => ['ExpenseController', 'index'],
            'expenses/store' => ['ExpenseController', 'store'],
            'expenses/delete' => ['ExpenseController', 'delete'],

            'purchases/manual' => ['PurchaseController', 'manual'],
            'purchases/manual-store' => ['PurchaseController', 'manualStore'],
            'purchases/invoices' => ['PurchaseController', 'invoices'],
            'purchases/invoices-store' => ['PurchaseController', 'invoiceStore'],

            'reports/daily-cash' => ['ReportController', 'dailyCash'],

            'sales/pos' => ['SaleController', 'pos'],
            'sales/history' => ['SaleController', 'history'],
            'sales/add-item' => ['SaleController', 'addItem'],
            'sales/remove-item' => ['SaleController', 'removeItem'],
            'sales/checkout' => ['SaleController', 'checkout'],
        ];

        return $routes[$path] ?? ['DashboardController', 'index'];
    }
}

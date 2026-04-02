<?php

class Router
{
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace(BASE_URL, '', $uri);
        $path = trim($path, '/');

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

            'suppliers' => ['SupplierController', 'index'],
            'suppliers/store' => ['SupplierController', 'store'],
            'suppliers/update' => ['SupplierController', 'update'],
            'suppliers/delete' => ['SupplierController', 'delete'],

            'expenses' => ['ExpenseController', 'index'],
            'expenses/store' => ['ExpenseController', 'store'],
            'expenses/delete' => ['ExpenseController', 'delete'],

            'sales/pos' => ['SaleController', 'pos'],
            'sales/add-item' => ['SaleController', 'addItem'],
            'sales/remove-item' => ['SaleController', 'removeItem'],
            'sales/checkout' => ['SaleController', 'checkout'],
        ];

        return $routes[$path] ?? ['DashboardController', 'index'];
    }
}

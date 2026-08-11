<?php

/**
 * Genera una URL correcta según si mod_rewrite está disponible o no
 * 
 * @param string $path Ruta de la aplicación (ej: 'login', 'products', 'sales/pos')
 * @return string URL completa (ej: '/dev/fiambreria/login' o '/dev/fiambreria/index.php?route=login')
 */
function route(string $path = ''): string
{
    $basePath = BASE_URL;
    
    if ($path === '' || $path === '/') {
        $path = 'dashboard';
    }
    
    // Intentar usar la URL amigable primero (si mod_rewrite funciona)
    // Si falla, el usuario será redirigido y luego usará ?route=
    return $basePath . '/' . ltrim($path, '/');
}

/**
 * Genera URL con fallback para hostings sin mod_rewrite
 * Esto se usa en los formularios POST y links críticos
 */
function routeFallback(string $path = ''): string
{
    if ($path === '' || $path === '/') {
        $path = 'dashboard';
    }
    
    // Devuelve la URL con parámetro route (funciona con y sin mod_rewrite)
    return BASE_URL . '/index.php?route=' . ltrim($path, '/');
}

/**
 * Obtiene la URL de un asset (CSS, JS, imágenes)
 * 
 * @param string $asset Ruta del asset relativa a /public/assets/
 * @return string URL completa del asset
 */
function asset(string $asset): string
{
    return ASSETS_URL . '/' . ltrim($asset, '/');
}

/**
 * Devuelve el token CSRF de la sesion actual, generandolo si no existe.
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Input hidden listo para insertar dentro de un <form method="post">.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Corta la ejecucion con 419 si el POST no trae un token CSRF valido.
 * Debe llamarse al principio de cada accion que procese un POST.
 */
function csrf_verify(): void
{
    $token = $_POST['_csrf'] ?? '';

    if ($token === '' || empty($_SESSION['_csrf_token']) || !hash_equals($_SESSION['_csrf_token'], $token)) {
        http_response_code(403);
        die('Token de seguridad invalido o expirado. Volve atras, recarga el formulario e intenta de nuevo.');
    }
}

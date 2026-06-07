<?php

// Fallback front controller for hostings where root .htaccess rewrite is disabled.
// Si mod_rewrite no funciona, parseamos la URL manualmente

if (php_sapi_name() !== 'cli') {
    // Obtener la ruta del REQUEST_URI
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $basePath = '/dev/fiambreria';
    
    // Si la URI contiene la ruta base, extraerla
    if (strpos($uri, $basePath) === 0) {
        $path = substr($uri, strlen($basePath));
        $path = trim($path, '/');
        
        // Si hay una ruta específica, pasarla como parámetro de consulta
        if ($path && $path !== 'public' && strpos($path, 'public/') !== 0) {
            $_GET['route'] = $path;
        }
    }
}

require __DIR__ . '/public/index.php';

<?php

session_start();

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../core/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$router = new Router();
$router->dispatch();

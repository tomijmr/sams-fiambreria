<?php

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../app/views/' . $view . '.php';
    }

    protected function redirect(string $path): void
    {
        // Usar routeFallback para garantizar que funciona con y sin mod_rewrite
        $url = routeFallback($path);
        header('Location: ' . $url);
        exit;
    }
}


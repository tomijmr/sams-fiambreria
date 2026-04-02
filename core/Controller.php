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
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}

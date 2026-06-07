<?php

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            // Usar routeFallback para garantizar que funciona con y sin mod_rewrite
            $loginUrl = routeFallback('login');
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    public static function userName(): string
    {
        return $_SESSION['user_name'] ?? 'Invitado';
    }
}

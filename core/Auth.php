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

    public static function role(): string
    {
        return $_SESSION['user_role'] ?? 'cajero';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (!self::isAdmin()) {
            http_response_code(403);
            die('No tenes permisos para acceder a esta seccion.');
        }
    }
}

<?php

// Cargar variables de entorno desde .env si existe (no versionado en git)
$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
			continue;
		}
		[$key, $value] = explode('=', $line, 2);
		$key = trim($key);
		$value = trim($value);
		if ($key !== '' && getenv($key) === false) {
			putenv($key . '=' . $value);
		}
	}
}

define('APP_NAME', 'SAMS - Kiosko');

// Detectar el ambiente (local vs hosting)
$isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
            strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);

// BASE_URL configurado según el ambiente
$configuredBaseUrl = getenv('APP_BASE_URL') ?: '';

if ($configuredBaseUrl !== '') {
	$baseUrl = rtrim($configuredBaseUrl, '/');
} else {
	if ($isLocal) {
		$baseUrl = '/dev/sams-fiambreria';
	} else {
		$baseUrl = '/dev/fiambreria';
	}
}

if ($baseUrl === '/') {
	$baseUrl = '';
}

define('BASE_URL', $baseUrl);

$assetsUrl = str_ends_with(BASE_URL, '/public') ? (BASE_URL . '/assets') : (BASE_URL . '/public/assets');
define('ASSETS_URL', rtrim($assetsUrl, '/'));

// Credenciales de BD según el ambiente
if ($isLocal) {
	// Credenciales locales (XAMPP)
	define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
	define('DB_NAME', getenv('DB_NAME') ?: 'sams_kiosko');
	define('DB_USER', getenv('DB_USER') ?: 'root');
	define('DB_PASS', getenv('DB_PASS') ?: '');
} else {
	// Credenciales de hosting: deben venir SIEMPRE del archivo .env (no versionado).
	// Ver .env.example para el formato.
	if (getenv('DB_NAME') === false || getenv('DB_USER') === false || getenv('DB_PASS') === false) {
		http_response_code(500);
		die('Falta configurar el archivo .env con las credenciales de la base de datos (ver .env.example).');
	}
	define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
	define('DB_NAME', getenv('DB_NAME'));
	define('DB_USER', getenv('DB_USER'));
	define('DB_PASS', getenv('DB_PASS'));
}

define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Argentina/Buenos_Aires');

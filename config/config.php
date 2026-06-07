<?php

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
	// Credenciales de hosting
	define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
	define('DB_NAME', getenv('DB_NAME') ?: 'c2632136_gaucho');
	define('DB_USER', getenv('DB_USER') ?: 'c2632136_gaucho');
	define('DB_PASS', getenv('DB_PASS') ?: 'pobe44KOfo');
}

define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Argentina/Buenos_Aires');

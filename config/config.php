<?php

define('APP_NAME', 'SAMS - Kiosko');

$configuredBaseUrl = getenv('APP_BASE_URL') ?: '';

if ($configuredBaseUrl !== '') {
	$baseUrl = rtrim($configuredBaseUrl, '/');
} else {
	$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
	$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
	$baseUrl = rtrim(dirname($scriptName), '/');

	// Si el front controller se ejecuta en /public por rewrite interno,
	// expone la app sin /public en la URL final.
	if ($baseUrl !== '' && str_ends_with($baseUrl, '/public')) {
		$publicLessBase = substr($baseUrl, 0, -7);
		if ($publicLessBase !== '' && str_starts_with($requestUri, $publicLessBase . '/')) {
			$baseUrl = $publicLessBase;
		}
	}
}

if ($baseUrl === '/') {
	$baseUrl = '';
}

define('BASE_URL', $baseUrl);

$assetsUrl = str_ends_with(BASE_URL, '/public') ? (BASE_URL . '/assets') : (BASE_URL . '/public/assets');
define('ASSETS_URL', rtrim($assetsUrl, '/'));

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sams_kiosko');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Argentina/Buenos_Aires');

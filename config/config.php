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

// define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
// define('DB_NAME', getenv('DB_NAME') ?: 'a0011086_sams');
// define('DB_USER', getenv('DB_USER') ?: 'a0011086');
// define('DB_PASS', getenv('DB_PASS') ?: 'PObitovi56');
// define('DB_CHARSET', 'utf8mb4');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'c2632136_gaucho');
define('DB_USER', getenv('DB_USER') ?: 'c2632136_gaucho');
define('DB_PASS', getenv('DB_PASS') ?: 'pobe44KOfo');
define('DB_CHARSET', 'utf8mb4');

date_default_timezone_set('America/Argentina/Buenos_Aires');

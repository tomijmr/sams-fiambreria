<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug SAMS Fiambrería</h2>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";

try {
    require_once __DIR__ . '/config/config.php';
    
    echo "<h3>Configuración cargada correctamente</h3>";
    echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
    echo "<p><strong>DB_HOST:</strong> " . DB_HOST . "</p>";
    echo "<p><strong>DB_NAME:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>DB_USER:</strong> " . DB_USER . "</p>";
    
    // Intentar conectar a BD
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "<p style='color: green;'><strong>✅ Conexión a BD exitosa</strong></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>" . get_class($e) . ":</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
}
?>

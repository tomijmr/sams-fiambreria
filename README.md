# SAMS - Kiosko

Sistema web para una fiambreria de barrio con:
- Control de stock (productos por unidad y por peso)
- Ventas rapidas con lector de codigo de barras
- Calculo automatico de precio por costo + % de ganancia
- Proveedores
- Gastos
- Panel con metricas diarias y alertas de stock bajo

## Stack
- PHP 8+
- MySQL / MariaDB
- HTML + CSS + Bootstrap 5
- MVC basico sin frameworks

## Instalacion (XAMPP/LAMPP)
1. Crear base de datos importando `database/sams_kiosko.sql`.
2. Ajustar credenciales en `config/config.php`.
3. Asegurar Apache con `mod_rewrite` activo.
4. Abrir en navegador: `http://localhost/dev/sams-fiambreria/public`

## Usuario inicial
- Usuario: `admin`
- Clave: `123456`

## Flujo POS para fiambres por gramos
1. Cargar producto con tipo `weight`, stock en kg y costo por kg.
2. El sistema calcula precio de venta por kg con `% ganancia`.
3. En ventas rapidas, escanear codigo e ingresar gramos (por ejemplo `250`).
4. El sistema calcula subtotal: precio_kg / 1000 * gramos.

## Estructura
- `app/controllers`: controladores
- `app/models`: modelos
- `app/views`: vistas
- `core`: base MVC
- `public`: punto de entrada y assets
- `database`: script SQL

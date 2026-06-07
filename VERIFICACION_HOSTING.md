# Verificación de Instalación - SAMS Fiambrería

Cuando el servidor de hosting esté nuevamente en línea, sigue estos pasos:

## 1. Verificar conectividad
```bash
curl -I https://jmrsolutions.com.ar/dev/fiambreria/
```

Debería responder con `HTTP/1.1 302 Found` (redirección al login)

## 2. Acceder a la página de debug
```
https://jmrsolutions.com.ar/dev/fiambreria/debug-hosting.php
```

Deberías ver:
- ✅ Configuración cargada correctamente
- ✅ BASE_URL: /dev/fiambreria
- ✅ DB_NAME: c2632136_gaucho
- ✅ Conexión a BD exitosa

## 3. Acceder al login
```
https://jmrsolutions.com.ar/dev/fiambreria/login
```

Debería cargar el formulario de login

## 4. Probar con credenciales
- Usuario: admin
- Clave: 123456

## Si sigue habiendo errores 403 o 500:

### A. Verificar permisos (via FTP o cPanel):
```
755 - directorios
644 - archivos .php
```

### B. Verificar que mod_rewrite está habilitado:
Contacta a tu proveedor de hosting y pide que habilite `mod_rewrite` en Apache

### C. Si mod_rewrite no está disponible:
La aplicación funciona sin .htaccess usando URLs con `index.php`:
```
https://jmrsolutions.com.ar/dev/fiambreria/public/index.php?route=login
```

## Notas importantes:
- La configuración `config/config.php` detecta automáticamente si está en localhost o hosting
- Los logs de error en hosting están en cPanel → Metrics → Error Log
- Las credenciales de BD (c2632136_gaucho) ya están correctas en la configuración

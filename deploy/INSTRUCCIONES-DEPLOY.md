# Instrucciones de Deploy — Regalos Purranque v2

## Pre-requisitos
- v2 testeada completamente en local
- Backup completo de v1 realizado
- Acceso a cPanel de HostGator (IP: 162.241.53.185)

---

## Paso 1: Backup de v1

1. Entrar a **cPanel** > **phpMyAdmin**
2. Seleccionar BD `purranque_regalos_purranque`
3. **Exportar** > Formato SQL > Descargar (guardar como `backup_v1_FECHA.sql`)
4. En cPanel > **File Manager** > Comprimir carpeta del sitio > descargar como `backup_v1_files.zip`
5. Guardar ambos archivos en local como respaldo

---

## Paso 2: Preparar BD en produccion

1. En cPanel > **phpMyAdmin** > seleccionar BD `purranque_regalos_purranque`
2. **NO borrar tablas v1** — el script de migracion las renombra automaticamente con sufijo `_v1`
3. Importar `database/schema.sql` (crea tablas v2 con `IF NOT EXISTS`)
   - El script de migracion tambien puede crear las tablas, pero es mas seguro importar schema.sql primero

**Nota:** NO importar `database/seed.sql`. Los datos reales vienen de la migracion v1.

---

## Paso 3: Subir archivos v2

### Estructura en el hosting

```
/home/purranqu/                       (directorio del usuario)
├── regalos-v2/                       (FUERA de public_html)
│   ├── app/
│   ├── config/
│   │   ├── app.php
│   │   └── database.php              (con credenciales de produccion)
│   ├── views/
│   ├── storage/
│   │   ├── backups/
│   │   ├── logs/
│   │   ├── cache/
│   │   └── temp/
│   ├── database/
│   ├── cron/
│   └── CLAUDE.md
│
└── public_html/
    └── regalos/                      (DOCUMENT ROOT del subdominio)
        ├── index.php                 (ajustar BASE_PATH)
        ├── .htaccess
        ├── robots.txt
        ├── sitemap.xml
        ├── manifest.json
        ├── mantenimiento.html
        └── assets/
            ├── css/
            ├── js/
            └── img/
```

### Opcion A: Todo en document root (mas simple para hosting compartido)

Si no puedes poner archivos fuera de `public_html`:

```
/home/purranqu/public_html/regalos/
├── index.php
├── .htaccess
├── robots.txt
├── assets/
├── app/                              (protegido por .htaccess)
├── config/
├── views/
├── storage/
├── database/
├── cron/
└── CLAUDE.md
```

En este caso, `BASE_PATH` en `index.php` queda como:
```php
define('BASE_PATH', __DIR__);
```

Y el `.htaccess` ya protege carpetas sensibles con las reglas existentes.

### Opcion B: Separado (recomendado)

1. Subir carpeta completa v2 a `/home/purranqu/regalos-v2/`
2. Copiar contenido de `public/` al document root del subdominio
3. Editar `index.php` del document root:
```php
define('BASE_PATH', '/home/purranqu/regalos-v2');
```

### Procedimiento de subida

1. En local, comprimir todo el proyecto en `regalos-v2.zip`
2. En cPanel > **File Manager** > subir `regalos-v2.zip`
3. Descomprimir en la ubicacion elegida
4. Renombrar carpeta actual del sitio v1: `regalos_v1_backup/`
5. Configurar el document root del subdominio para que apunte a la nueva carpeta

---

## Paso 4: Configurar archivos

### config/database.php
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'purranque_regalos_purranque');
define('DB_USER', 'purranque_admin');
define('DB_PASS', 'CONTRASEÑA_REAL');    // Completar
define('DB_CHARSET', 'utf8mb4');
```

### config/app.php
Verificar que estas lineas sean correctas:
```php
define('APP_ENV', 'production');
define('SITE_URL', 'https://regalos.purranque.info');
```

### public/index.php
Ajustar `BASE_PATH` segun la estructura elegida:
```php
// Opcion A (todo en document root):
define('BASE_PATH', __DIR__);

// Opcion B (separado):
define('BASE_PATH', '/home/purranqu/regalos-v2');
```

---

## Paso 5: Permisos

En cPanel > **File Manager**, dar permisos **755** a:

- `storage/backups/`
- `storage/logs/`
- `storage/cache/`
- `storage/temp/`
- `public/assets/img/logos/`
- `public/assets/img/portadas/`
- `public/assets/img/galeria/`
- `public/assets/img/banners/`
- `public/assets/img/noticias/`
- `public/assets/img/og/`
- `public/assets/img/config/`

Seleccionar carpeta > clic derecho > **Change Permissions** > 755 > aplicar.

---

## Paso 6: Habilitar HTTPS

En `public/.htaccess`, descomentar las lineas de HTTPS:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Paso 7: Migrar datos

1. Copiar `database/migrations/migrate_v1_to_v2.php` al document root como `migrate.php`
2. Editar credenciales BD dentro del archivo si son diferentes
3. **DRY RUN** (verificacion, no modifica nada):
   ```
   https://regalos.purranque.info/migrate.php?key=regalos_migrate_2026_secure&mode=dry
   ```
4. Revisar que no hay errores en el dry run
5. **EJECUTAR** migracion real:
   ```
   https://regalos.purranque.info/migrate.php?key=regalos_migrate_2026_secure&mode=run
   ```
6. **ELIMINAR** `migrate.php` inmediatamente

---

## Paso 8: Verificacion post-deploy

1. Copiar `deploy/verify.php` al document root
2. Acceder:
   ```
   https://regalos.purranque.info/verify.php?key=regalos_verify_2026
   ```
3. Verificar que todas las comprobaciones pasan
4. **ELIMINAR** `verify.php` inmediatamente

### Verificaciones manuales

1. Home carga correctamente
2. Categorias visibles con comercios
3. Fechas especiales (3 tipos) visibles
4. Ficha de comercio funciona (`/comercio/slug`)
5. Noticias cargan
6. Mapa centrado en Plaza de Purranque
7. Busqueda funciona
8. Login admin funciona (`/admin`)
9. Dashboard con datos reales
10. CRUD de comercios funciona
11. Subida de imagenes funciona
12. Resenas funcionan
13. Panel SEO funciona
14. Panel mantenimiento funciona
15. Salud del sistema score > 80%
16. SSL activo (HTTPS)
17. sitemap.xml accesible
18. robots.txt correcto
19. Open Graph preview correcto (compartir en Facebook)
20. Responsive en movil

---

## Paso 9: Configurar Cron Jobs

En cPanel > **Cron Jobs**:

### Backup diario (2:00 AM)
```
Comando:  /usr/local/bin/php /home/purranqu/regalos-v2/cron/backup-auto.php
Minuto:   0
Hora:     2
Dia:      *
Mes:      *
Dia sem:  *
```

### Analytics diario (12:05 AM)
```
Comando:  /usr/local/bin/php /home/purranqu/regalos-v2/cron/analytics-daily.php
Minuto:   5
Hora:     0
Dia:      *
Mes:      *
Dia sem:  *
```

**Nota:** Ajustar la ruta `/home/purranqu/` segun el usuario real de cPanel. Para verificar, ir a cPanel > Terminal (si esta disponible) o revisar File Manager la ruta completa.

---

## Paso 10: Google Search Console

1. Ir a [Google Search Console](https://search.google.com/search-console)
2. Seleccionar propiedad `regalos.purranque.info`
3. **Sitemaps** > Agregar: `https://regalos.purranque.info/sitemap.xml`
4. **Inspeccion de URLs** > Solicitar indexacion de la URL principal
5. Verificar que no hay errores de cobertura

---

## Paso 11: Limpieza post-migracion

Despues de confirmar que todo funciona:

1. Eliminar `migrate.php` del document root (si no se hizo ya)
2. Eliminar `verify.php` del document root (si no se hizo ya)
3. Las tablas v1 (con sufijo `_v1`) quedan como respaldo en la BD
4. Despues de 1-2 semanas, se pueden eliminar las tablas `_v1` desde phpMyAdmin
5. Eliminar la carpeta `regalos_v1_backup/` del servidor despues de confirmar estabilidad

---

## Paso 12: Monitoreo primera semana

Ver archivo `deploy/post-deploy-monitor.md` para el plan de monitoreo detallado.

---

## Rollback (si algo falla)

1. En cPanel > File Manager:
   - Renombrar carpeta v2 actual a `regalos_v2_failed/`
   - Renombrar `regalos_v1_backup/` de vuelta a su nombre original
2. En phpMyAdmin:
   - Las tablas v1 tienen sufijo `_v1`, renombrar quitando el sufijo
   - O restaurar el backup SQL de v1
3. El sitio v1 deberia volver a funcionar inmediatamente

# Deploy: Parche Legal — Febrero 2026
# Cumplimiento Ley 19.628 + Ley 21.719

## Contenido del paquete: parche-legal.zip

### Archivos NUEVOS (copiar tal cual respetando rutas):
```
app/Controllers/Public/DerechosController.php
app/Controllers/Api/ConsentimientoApiController.php
views/public/derechos.php
views/partials/cookie-banner.php
views/emails/arco-admin.php
views/emails/arco-confirmacion.php
assets/css/derechos.css
```

### Archivos MODIFICADOS (reemplazar los existentes):
```
config/routes.php
views/layouts/public.php
views/partials/footer.php
views/public/terminos.php
views/public/privacidad.php
app/Services/Notification.php
```

### SQL:
```
sql/01_parche_legal.sql
```

---

## Instrucciones de Deploy

### 1. BACKUP (obligatorio antes de cualquier cambio)

En cPanel > phpMyAdmin:
- Exportar BD `purranque_regalos_v2` completa (SQL)
- Guardar como `backup_pre_parche_legal_20260220.sql`

En cPanel > File Manager:
- Comprimir carpeta del sitio como backup

### 2. Ejecutar SQL

En cPanel > phpMyAdmin > seleccionar BD `purranque_regalos_v2`:
1. Ir a pestaña "Importar"
2. Subir `sql/01_parche_legal.sql`
3. Ejecutar

Esto crea 3 tablas:
- `consentimientos` (registro de cookies)
- `solicitudes_arco` (formularios de derechos)
- `registro_tratamiento` (10 registros iniciales)

Luego ejecutar manualmente en pestaña "SQL":
```sql
ALTER TABLE resenas ADD COLUMN moderado TINYINT(1) NOT NULL DEFAULT 0 AFTER estado;
ALTER TABLE resenas ADD COLUMN moderado_por INT DEFAULT NULL AFTER moderado;
ALTER TABLE resenas ADD COLUMN moderado_at DATETIME DEFAULT NULL AFTER moderado_por;
UPDATE resenas SET moderado = 1 WHERE estado = 'aprobada';
```

### 3. Subir archivos

En cPanel > File Manager:
1. Subir `parche-legal.zip` a la raíz del sitio
2. Extraer (Extract)
3. Verificar que los archivos se ubicaron en las rutas correctas
4. Eliminar `parche-legal.zip` del servidor

### 4. Headers de seguridad (Nginx / .htaccess)

Si el hosting usa Apache (HostGator compartido), agregar al `.htaccess` existente:
```apache
# Security Headers — Parche Legal
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(self)"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

### 5. Verificación

Checks automáticos (en phpMyAdmin > SQL):
```sql
-- Debe mostrar 3 tablas
SHOW TABLES LIKE 'consentimientos';
SHOW TABLES LIKE 'solicitudes_arco';
SHOW TABLES LIKE 'registro_tratamiento';

-- Debe dar 10
SELECT COUNT(*) as total FROM registro_tratamiento;

-- Debe mostrar columna moderado
DESCRIBE resenas;
```

Checks en navegador:
- [ ] https://regalospurranque.cl/ → banner de cookies aparece (modo incógnito)
- [ ] Aceptar cookies → banner desaparece, no vuelve al recargar
- [ ] https://regalospurranque.cl/derechos → 5 tarjetas + 4 casos frecuentes
- [ ] Click en "Eliminar mis datos" → formulario con checkboxes
- [ ] Marcar "Soy comerciante" → aparece campo nombre del comercio
- [ ] Enviar solicitud de prueba → confirmación + email
- [ ] https://regalospurranque.cl/terminos → secciones 2.1, 6.1, 6.2 visibles
- [ ] https://regalospurranque.cl/privacidad → referencia Ley 21.719, DPD, enlace /derechos
- [ ] Footer → enlace "Ejercicio de Derechos" visible
- [ ] Headers de seguridad: abrir DevTools > Network > ver headers de respuesta

### 6. Rollback (si algo falla)

1. En phpMyAdmin: restaurar backup SQL
2. En File Manager: restaurar archivos desde backup
3. Las tablas nuevas (consentimientos, solicitudes_arco, registro_tratamiento) se pueden eliminar con DROP TABLE si es necesario

# Checklist Pre-Deploy — Regalos Purranque v2

## Codigo
- [ ] Todas las fases completadas (1-6) y testeadas
- [ ] Sin errores PHP en log (storage/logs/)
- [ ] Sin console.error en navegador
- [ ] config/database.php con credenciales de produccion
- [ ] config/app.php con APP_ENV = 'production'
- [ ] config/app.php con SITE_URL = 'https://regalos.purranque.info'
- [ ] .htaccess con HTTPS habilitado (lineas descomentadas)
- [ ] robots.txt correcto
- [ ] sitemap.xml generado

## Base de datos
- [ ] schema.sql probado en MySQL 8.x
- [ ] seed.sql con datos correctos (categorias, fechas, admin)
- [ ] Usuario admin con contrasena segura (NO 'password')
- [ ] Todas las tablas tienen indices correctos
- [ ] ENUM de fechas_especiales tiene los 3 tipos (personal, calendario, comercial)

## Seguridad
- [ ] CSRF en todos los formularios POST
- [ ] Contrasena admin cambiada (no usar la del seed)
- [ ] storage/ protegido con .htaccess (Deny from all)
- [ ] No hay datos de desarrollo en codigo
- [ ] APP_DEBUG = false en produccion (automatico con APP_ENV)
- [ ] migrate_v1_to_v2.php tiene clave secreta configurada
- [ ] verify.php tiene clave secreta configurada
- [ ] phpinfo deshabilitado en produccion (APP_ENV guard)
- [ ] No hay contraseñas hardcodeadas en el codigo fuente

## SEO
- [ ] Meta tags en todas las paginas publicas
- [ ] Schema.org LocalBusiness configurado
- [ ] Open Graph con imagenes por defecto
- [ ] Canonical URLs correctas
- [ ] Redirects 301 preparados (redirects_v1_urls.sql)
- [ ] Slugs de comercios preservados exactos desde v1
- [ ] Slugs de categorias preservados exactos desde v1
- [ ] Slugs de fechas mapeados correctamente (v1 → v2)
- [ ] sitemap.xml incluye todas las paginas

## Imagenes
- [ ] Todas las imagenes de v1 disponibles para copiar
- [ ] Rutas de imagenes correctas en BD
- [ ] Permisos de escritura en carpetas de imagenes
- [ ] Carpetas de imagenes creadas (logos, portadas, galeria, banners, noticias, og)

## Testing local
- [ ] Home carga < 3 segundos
- [ ] Todas las paginas publicas funcionan sin error
- [ ] Admin login funciona
- [ ] CRUD de todos los modulos funciona
- [ ] Subida de imagenes funciona
- [ ] Resenas se pueden crear y moderar
- [ ] Backup se genera correctamente
- [ ] Responsive funciona en movil (Chrome DevTools)
- [ ] No hay enlaces rotos (navegar todas las secciones)
- [ ] Busqueda retorna resultados correctos
- [ ] Mapa muestra marcadores

## Archivos de deploy
- [ ] database/migrations/migrate_v1_to_v2.php listo
- [ ] database/migrations/redirects_v1_urls.sql listo
- [ ] deploy/verify.php listo
- [ ] deploy/INSTRUCCIONES-DEPLOY.md revisado
- [ ] config/database.php.example actualizado
- [ ] .gitignore actualizado

## Backups
- [ ] Backup de BD v1 descargado y guardado en local
- [ ] Backup de archivos v1 descargado y guardado en local
- [ ] Plan de rollback claro (ver INSTRUCCIONES-DEPLOY.md)

# Monitoreo Post-Deploy — Primera Semana

## Dia 1
- [ ] Verificar que el sitio carga correctamente en navegador
- [ ] Verificar HTTPS activo (candado en barra de direcciones)
- [ ] Verificar login admin funciona
- [ ] Ejecutar verify.php y confirmar todas las verificaciones pasan
- [ ] Revisar log de errores PHP (storage/logs/php_errors.log)
- [ ] Crear primer backup manual desde Admin > Mantenimiento > Backups
- [ ] Verificar que las URLs v1 (.php?slug=) redireccionan correctamente a v2
- [ ] Probar 3-5 paginas de comercios, categorias y fechas

## Dia 2-3
- [ ] Verificar que los backups automaticos (cron) se generaron
- [ ] Revisar Google Search Console por errores nuevos
- [ ] Verificar que analytics esta registrando visitas (Admin > Dashboard)
- [ ] Probar crear/editar un comercio desde el admin
- [ ] Probar sistema de resenas (crear una resena de prueba)
- [ ] Verificar que el mapa carga correctamente
- [ ] Revisar storage/logs/ por errores acumulados

## Dia 4-7
- [ ] Comparar trafico con semana anterior en Google Search Console
- [ ] Verificar que no hay errores 404 nuevos (Search Console > Cobertura)
- [ ] Revisar rendimiento con PageSpeed Insights (score > 70)
- [ ] Verificar espacio en disco (Admin > Mantenimiento > Salud)
- [ ] Limpiar archivos temporales de migracion si quedan
- [ ] Verificar que el sitemap.xml esta actualizado
- [ ] Probar compartir un comercio en redes sociales (Open Graph)
- [ ] Verificar la busqueda con varios terminos

## Despues de 1 semana
- [ ] Eliminar carpeta v1 backup del servidor (guardar solo en local)
- [ ] Eliminar tablas _v1 de la BD desde phpMyAdmin
- [ ] Eliminar script verify.php si aun existe
- [ ] Verificar posicionamiento SEO estable en Google
- [ ] Verificar que los redirects 301 estan funcionando
- [ ] Documentar cualquier issue encontrado
- [ ] Celebrar el deploy exitoso

## Indicadores de exito
- Sin errores 500 en las primeras 24 horas
- Sin caida significativa de trafico (< 20% de diferencia)
- Todas las URLs indexadas responden correctamente
- Score de salud del sistema > 80%
- Backups automaticos funcionando
- Login admin funciona sin problemas

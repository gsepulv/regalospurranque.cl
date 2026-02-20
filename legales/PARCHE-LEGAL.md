# PARCHE-LEGAL.md — Instrucciones para Claude Code
# Este archivo NO reemplaza el CLAUDE.md del proyecto. Va en la carpeta legal/.

## Contexto

**Regalos Purranque** (regalospurranque.cl / v2.regalos.purranque.info) es un directorio comercial de Purranque, Región de Los Lagos, Chile. Proyecto de PurranQUE.INFO.

- **Stack:** PHP puro (sin frameworks), MySQL 8, Apache (HostGator compartido)
- **Repo:** https://github.com/gsepulv/regalospurranque.cl (PÚBLICO)
- **Producción:** https://v2.regalos.purranque.info
- **Servidor:** HostGator compartido, cPanel, SIN SSH, SIN acceso root

## Flujo de Deploy

```
PC Local (Claude Code) → git push → GitHub → cPanel "Update from Remote" → "Deploy HEAD Commit" → Producción
```

### Paso a paso:
1. Claude Code trabaja en `C:\Proyectos\regalospurranque.cl\` (PC local de Gustavo)
2. Claude Code hace los cambios en archivos PHP, HTML, CSS, .htaccess, etc.
3. Claude Code hace `git add`, `git commit`, `git push`
4. **Gustavo va a cPanel** → Git Version Control → Administrar repo → Pull or Deploy
5. Clic en **"Update from Remote"** (trae cambios de GitHub)
6. Clic en **"Deploy HEAD Commit"** (copia archivos a `/home/purranque/v2.regalos.purranque.info/`)
7. El deploy lo ejecuta `.cpanel.yml` que copia todo a la carpeta del sitio

### Archivos que controla `.cpanel.yml`:
```yaml
---
deployment:
  tasks:
    - export DEPLOYPATH=/home/purranque/v2.regalos.purranque.info/
    - /bin/cp -R * $DEPLOYPATH
    - /bin/cp .htaccess $DEPLOYPATH 2>/dev/null; true
```

## Restricciones Críticas

- **REPO PÚBLICO:** NUNCA incluir credenciales (BD, passwords, API keys) en ningún archivo
- **NUNCA ejecutar comandos mysql.** La BD la gestiona Gustavo vía phpMyAdmin en cPanel
- **Sin SSH:** No hay acceso shell al servidor. Todo cambio llega vía git push + deploy
- **Sin sudo/root:** Es hosting compartido
- **Apache, NO Nginx:** Headers van en `.htaccess`, no en archivos nginx.conf
- **Archivo de config BD** debe estar en `.gitignore` (no versionado, solo en servidor)

## Estructura del Proyecto

```
C:\Proyectos\regalospurranque.cl\
├── app/              ← Lógica PHP (controladores, modelos, helpers)
├── assets/           ← CSS, JS, imágenes
├── config/           ← Configuración (BD en .gitignore)
├── cron/             ← Tareas programadas
├── database/         ← Esquemas SQL
├── deploy/           ← Scripts de deploy
├── storage/          ← Archivos subidos
├── views/            ← Vistas/templates PHP
├── legal/            ← CARPETA NUEVA: archivos del parche legal
├── .cpanel.yml       ← Config deploy HostGator
├── .htaccess         ← Rewrite rules + headers seguridad
├── .gitignore
├── CLAUDE.md         ← Doc original del proyecto (NO TOCAR)
├── index.php         ← Entry point
├── router.php        ← Sistema de rutas
└── ...
```

## Tarea: Parche Legal (Febrero 2026)

Cierre de 7 brechas legales (Auditoría C.4) para cumplimiento de Ley 19.628 y Ley 21.719.

### Archivos del paquete (carpeta `legal/`):

```
legal/
├── sql/
│   └── 01_parche_legal.sql           → 3 tablas + datos iniciales
├── php/
│   ├── api_consentimiento.php        → POST /api/consentimiento
│   └── pagina_derechos.php           → Página /derechos (formularios ARCO)
├── templates/
│   ├── banner-cookies.html           → Banner cookies (HTML+CSS+JS standalone)
│   ├── derechos.css                  → Estilos para /derechos
│   └── nginx-security-headers.conf   → REFERENCIA SOLAMENTE (usar .htaccess)
└── textos-legales/
    └── clausulas-nuevas.txt          → 8 bloques para /terminos y /privacidad
```

---

## PASO 1 — BACKUP (Gustavo en cPanel)

> Claude Code NO ejecuta este paso. Indicarle a Gustavo.

**BD:** phpMyAdmin → seleccionar BD → Exportar → descargar .sql
**Archivos:** cPanel File Manager → carpeta del sitio → Compress → descargar .zip

---

## PASO 2 — EJECUTAR SQL (Gustavo en phpMyAdmin)

> Claude Code NO ejecuta este paso. Preparar el SQL y avisarle a Gustavo.

Archivo: `legal/sql/01_parche_legal.sql`

Gustavo ejecuta en phpMyAdmin (Importar o copiar/pegar en pestaña SQL).

Crea 3 tablas:

### `consentimientos` (registro de cookies)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| session_id | VARCHAR(128) | ID sesión visitante |
| ip | VARCHAR(45) | IP visitante |
| tipo | ENUM('cookies_esenciales','cookies_todas') | Qué aceptó |
| user_agent | VARCHAR(512) | Navegador |
| created_at | DATETIME | Fecha aceptación |

### `solicitudes_arco` (formularios de derechos)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| tipo | ENUM('acceso','rectificacion','cancelacion','oposicion','portabilidad') | Derecho |
| nombre | VARCHAR(255) | Nombre solicitante |
| email | VARCHAR(255) | Email para respuesta |
| rut | VARCHAR(12) | RUT (opcional) |
| descripcion | TEXT | Detalle + metadatos |
| estado | ENUM('recibida','en_proceso','resuelta','rechazada') | Estado |
| respuesta | TEXT | Respuesta admin |
| ip | VARCHAR(45) | IP solicitante |
| fecha_solicitud | DATETIME | Fecha envío |
| fecha_respuesta | DATETIME | Fecha resolución |
| fecha_limite | DATETIME (calculado) | +14 días |

### `registro_tratamiento` (Ley 21.719)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| dato_personal | VARCHAR(255) | Ej: "Nombre", "Email" |
| fuente | ENUM | Origen del dato |
| finalidad | VARCHAR(512) | Para qué se usa |
| base_legal | VARCHAR(255) | Fundamento legal |
| plazo_conservacion | VARCHAR(255) | Cuánto se guarda |
| medidas_seguridad | VARCHAR(512) | Cómo se protege |

El SQL inserta 10 registros iniciales en `registro_tratamiento`.

---

## PASO 3 — INTEGRAR BANNER DE COOKIES (Claude Code)

Archivo fuente: `legal/templates/banner-cookies.html`

1. Encontrar el layout principal (buscar archivo con `</body>`)
2. Pegar contenido de `banner-cookies.html` justo ANTES de `</body>`
3. El banner es standalone: HTML + CSS + JS en un solo bloque
4. Al aceptar, envía POST a `/api/consentimiento.php`

---

## PASO 4 — INTEGRAR API DE CONSENTIMIENTO (Claude Code)

Archivo fuente: `legal/php/api_consentimiento.php`

1. Copiar a carpeta de API del proyecto (buscar dónde están los endpoints)
2. Ajustar conexión a BD: usar el mismo `require`/`include` del proyecto
3. Verificar que `/api/consentimiento.php` sea accesible por POST

**IMPORTANTE:** El archivo de config BD NO está en el repo. Usar el patrón existente.

---

## PASO 5 — INTEGRAR PÁGINA /derechos (Claude Code)

Archivos fuente:
- `legal/php/pagina_derechos.php` — Controlador completo
- `legal/templates/derechos.css` — Estilos

1. Copiar `pagina_derechos.php` a carpeta de páginas del proyecto
2. Ajustar conexión a BD (mismo patrón del proyecto)
3. Agregar ruta `/derechos` en `router.php`
4. Agregar CSS: al final de hoja de estilos principal o como archivo separado
5. Adaptar al layout del sitio (header/footer)

### La página tiene 2 pasos:

**Paso 1:** 5 tarjetas clickeables (Acceso, Rectificación, Cancelación, Oposición, Portabilidad) + 4 accesos rápidos

**Paso 2:** Formulario específico adaptado al derecho seleccionado

### Formulario de Cancelación (el más completo):
- Checkbox "Soy comerciante" → muestra campo nombre comercio
- Selector "Motivo de baja" (5 opciones)
- Checkboxes "Qué eliminar" (comercio, reseñas, cuenta, otro)
- Campos comunes: nombre, email, RUT (opc), teléfono (opc), descripción

### Seguridad:
- Rate limiting: máx 3 solicitudes por email en 24h
- Validación server-side
- Metadatos automáticos en descripción

### Emails automáticos:
- Al admin (contacto@purranque.info): todos los datos + fecha límite
- Al solicitante: confirmación + número seguimiento + plazo 10 días hábiles

### URLs directas:
- `/derechos?tipo=cancelacion` → formulario de baja
- `/derechos?tipo=acceso` → formulario de consulta

---

## PASO 6 — HEADERS DE SEGURIDAD EN .htaccess (Claude Code)

Agregar al inicio del `.htaccess` existente (ANTES de RewriteRule):

```apache
# === Headers de Seguridad (Parche Legal Feb 2026) ===
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(self)"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
# === Fin Headers de Seguridad ===
```

NO tocar el resto del `.htaccess`.

---

## PASO 7 — ACTUALIZAR TEXTOS LEGALES (Claude Code)

Archivo fuente: `legal/textos-legales/clausulas-nuevas.txt`

### En /terminos:
| # | Qué agregar | Dónde |
|---|-------------|-------|
| 1 | Sección 6.1 — Indemnización | Después de sección 6 |
| 2 | Sección 6.2 — Licencia de Contenido | Después de 6.1 |
| 3 | Sección 2.1 — Estado BETA | Después de sección 2 |

### En /privacidad:
| # | Qué agregar | Dónde |
|---|-------------|-------|
| 4 | Sección 6 — Derechos del Titular (REEMPLAZO) | Reemplazar sección 6 |
| 5 | Sección 6.1 — Registro Actividades Tratamiento | Después de nueva sección 6 |
| 6 | Sección 12.1 — DPD (Gustavo Sepúlveda) | Antes de Contacto |
| 7 | Referencia Ley 21.719 | Sección 1, ampliar texto |

### En footer:
| 8 | Enlace "Ejercicio de Derechos" → /derechos | Sección Legal, después de "Política de Contenidos" |

---

## PASO 8 — COMMIT Y PUSH (Claude Code)

```bash
git add .
git commit -m "feat: parche legal Ley 21.719 - banner cookies, formularios ARCO, headers seguridad, clausulas legales"
git push
```

Luego avisarle a Gustavo que vaya a cPanel → Git Version Control → "Update from Remote" → "Deploy HEAD Commit".

---

## PASO 9 — VERIFICACIÓN

### Gustavo en phpMyAdmin:
- [ ] Tabla `consentimientos` existe
- [ ] Tabla `solicitudes_arco` existe
- [ ] Tabla `registro_tratamiento` existe con 10 registros

### En navegador (después del deploy):
- [ ] https://v2.regalos.purranque.info en modo incógnito → banner de cookies
- [ ] Aceptar cookies → banner desaparece, no vuelve
- [ ] https://v2.regalos.purranque.info/derechos → 5 tarjetas + 4 accesos rápidos
- [ ] Click "Eliminar mis datos" → formulario con checkboxes
- [ ] Marcar "Soy comerciante" → aparece campo nombre comercio
- [ ] Enviar solicitud prueba → email a contacto@purranque.info
- [ ] /terminos tiene secciones 2.1, 6.1, 6.2
- [ ] /privacidad referencia Ley 21.719 y tiene DPD
- [ ] Footer incluye "Ejercicio de Derechos"

### Verificar headers:
```bash
curl -I https://v2.regalos.purranque.info 2>/dev/null | grep -i 'x-frame\|x-content\|referrer'
```

---

## Reglas para Claude Code

- **NUNCA incluir credenciales** en archivos del repo (es público)
- **NUNCA ejecutar mysql** directamente — la BD la gestiona Gustavo
- **NO TOCAR el CLAUDE.md** del proyecto — tiene la doc original
- **Explorar primero** la estructura antes de modificar
- **Reutilizar patrón de conexión BD** del proyecto (require/include)
- **Headers en .htaccess** (Apache), no Nginx
- **Después de terminar:** git add, commit, push + avisar a Gustavo para deploy en cPanel

## Contacto

- **Responsable:** Gustavo Sepúlveda Sánchez
- **Email:** contacto@purranque.info
- **Proyecto:** PurranQUE.INFO
- **Ubicación:** Purranque, Región de Los Lagos, Chile

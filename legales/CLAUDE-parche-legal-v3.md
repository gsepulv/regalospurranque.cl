# CLAUDE.md — regalospurranque.cl

## Proyecto

**Regalos Purranque** es un directorio comercial de la comuna de Purranque, Región de Los Lagos, Chile. Permite a comerciantes locales publicar sus negocios y a usuarios dejar reseñas. Es un proyecto de **PurranQUE.INFO** (contacto@purranque.info).

- **URL del sitio:** https://v2.regalos.purranque.info
- **Dominio de producción futuro:** regalospurranque.cl
- **Hosting:** HostGator (hosting compartido con cPanel)
- **Repositorio Git:** https://github.com/gsepulv/regalospurranque.cl
- **Ruta en servidor:** `/home/purranque/v2.regalos.purranque.info/`
- **Cuenta cPanel:** purranque

## Stack Tecnológico

- **Backend:** PHP puro (sin Laravel, sin WordPress, sin frameworks)
- **Base de datos:** MySQL (administrable desde phpMyAdmin en cPanel)
- **Servidor:** Apache (HostGator hosting compartido) — **NO es Nginx, NO es VPS**
- **SSL:** Certificado del hosting (HostGator)
- **Frontend:** HTML + CSS + JS vanilla (sin React, sin Vue)
- **Panel de administración:** cPanel (acceso a archivos, BD, dominios, emails)
- **Acceso a archivos:** cPanel File Manager, FTP, o SSH si está habilitado
- **No usa:** Composer, npm en producción, ni sistemas de build

### Diferencias importantes por ser HostGator (hosting compartido):
- **NO hay acceso a configuración de Nginx** → Los headers de seguridad se agregan vía `.htaccess` (Apache), no vía archivos de configuración de Nginx.
- **NO hay acceso root/sudo** → No se puede ejecutar `sudo nginx -t`, `systemctl`, ni instalar paquetes del sistema.
- **La BD se administra desde phpMyAdmin** (cPanel) o por SSH si está habilitado.
- **La carpeta raíz del sitio** está dentro de la estructura de cPanel (típicamente `public_html/` o un subdirectorio/subdominio).

## Estructura del Sitio en HostGator

El sitio **v2.regalos.purranque.info** tiene su carpeta raíz dentro de la estructura de cPanel de HostGator. Antes de hacer cualquier cambio, **explorar el proyecto** para encontrar:

- **Carpeta raíz del sitio:** Ubicar dónde está el `index.php` principal. Según cPanel, la ruta es:
  ```
  /home/purranque/v2.regalos.purranque.info
  ```
  Los archivos del paquete legal están en:
  ```
  /home/purranque/v2.regalos.purranque.info/legal/
  ```
- **Conexión a BD:** Buscar el archivo con las credenciales MySQL (`$host`, `$dbname`, `$user`, `$pass` o un `require`/`include` centralizado). En HostGator el host suele ser `localhost`. Reutilizar ese patrón en los archivos nuevos.
- **Router / Sistema de rutas:** Buscar cómo se resuelven las URLs (puede ser un `switch`, un array asociativo, `.htaccess` con mod_rewrite, o similar). Ahí se agrega la ruta `/derechos`.
- **Layout principal:** Buscar el archivo que contiene `</body>` (típicamente `footer.php`, `layout.php`, `base.php`, o similar). Ahí se inserta el banner de cookies.
- **Footer:** Buscar la sección "Legal" del footer donde están los enlaces a `/terminos`, `/privacidad`, `/contenidos`. Ahí se agrega el enlace a `/derechos`.
- **Páginas legales:** Buscar los archivos de `/terminos` y `/privacidad` para agregar las nuevas cláusulas.
- **Tabla de reseñas:** Verificar el nombre real de la tabla (puede ser `resenas`, `reviews`, `opiniones`, etc.) para el ALTER TABLE de moderación.
- **Archivo .htaccess:** Verificar si existe en la raíz del sitio. Se usará para agregar headers de seguridad (en lugar de configuración Nginx).

## Cómo se ejecuta Claude Code en este proyecto

Claude Code se ejecuta **por SSH directo en el servidor de HostGator**. Esto significa:

- Claude Code trabaja directamente sobre los archivos en `/home/purranque/v2.regalos.purranque.info/`
- **Los cambios son inmediatos** en el servidor — no hay git push/pull intermedio
- El sitio https://v2.regalos.purranque.info refleja los cambios al instante
- El `CLAUDE.md` y la carpeta `legal/` ya están en el servidor cuando Claude Code inicia

### Para iniciar la sesión de trabajo:
```bash
# Conectar por SSH a HostGator
ssh purranque@[servidor-hostgator]

# Ir a la carpeta del sitio
cd /home/purranque/v2.regalos.purranque.info

# Iniciar Claude Code (el CLAUDE.md se lee automáticamente)
claude
```

### Importante:
- **Todo cambio es en vivo.** No hay entorno de staging. Por eso el backup (Paso 1) es obligatorio.
- Si algo sale mal, restaurar desde el backup inmediatamente.
- Después de terminar, hacer `git add . && git commit -m "Parche legal Feb 2026" && git push` para que los cambios queden respaldados en GitHub.

## Base de Datos

> **IMPORTANTE — REPO PÚBLICO:** Este repositorio es público en GitHub. **NUNCA incluir credenciales de BD en ningún archivo del repo.**

### Quién hace qué:
- **Gustavo ejecuta manualmente** todo lo relacionado con la base de datos: crear tablas, ejecutar SQL, verificar datos en phpMyAdmin.
- **Claude Code NO ejecuta comandos mysql** directamente. En su lugar, genera los archivos SQL necesarios y le indica a Gustavo qué ejecutar.
- Los archivos PHP que necesitan conexión a BD deben usar el **mismo patrón** que ya usa el proyecto (un `require`/`include` a un archivo de configuración que NO está en el repo).

### Flujo para cambios en BD:
1. Claude Code prepara o indica el SQL necesario
2. Gustavo lo ejecuta en **phpMyAdmin** (cPanel) o por terminal SSH
3. Gustavo confirma que se ejecutó correctamente
4. Claude Code continúa con el siguiente paso

### Archivo de configuración de BD:
El proyecto debe tener un archivo de conexión a BD (ej: `config.php`, `db.php`, `conexion.php`) que **está en el servidor pero NO en el repo** (debe estar en `.gitignore`). Claude Code debe detectar cuál es ese archivo y reutilizar el mismo `require`/`include` en los archivos nuevos que cree.

---

## Tarea Actual: Parche Legal (Febrero 2026)

Cierre de 7 brechas legales identificadas en la Auditoría C.4 para cumplimiento de **Ley 19.628** y **Ley 21.719** (Protección de Datos Personales de Chile).

Los archivos del parche están en la carpeta **`legal/`** dentro de la raíz del sitio en HostGator.

Ruta completa en el servidor: `/home/purranque/v2.regalos.purranque.info/legal/`

> **Nota:** Gustavo subirá todos los archivos del paquete a esta carpeta `legal/` directamente en el servidor de HostGator (vía cPanel File Manager, FTP, o SSH). Claude Code debe buscar los archivos ahí.

---

## PASO 1 — BACKUP

Antes de cualquier cambio:

### Opción A: Desde SSH (si está habilitado en HostGator)
```bash
# Backup de la base de datos
mysqldump -u [usuario] -p [nombre_bd] > ~/backup_pre_parche_$(date +%Y%m%d_%H%M%S).sql

# Backup del directorio del sitio
cp -r /home/purranque/v2.regalos.purranque.info ~/backup_proyecto_$(date +%Y%m%d_%H%M%S)
```

### Opción B: Desde cPanel (más común en HostGator)
1. **BD:** Ir a phpMyAdmin → seleccionar la BD → pestaña "Exportar" → "Exportar rápido" → descargar .sql
2. **Archivos:** Ir a File Manager → carpeta del sitio → seleccionar todo → "Compress" → descargar el .zip

**No continuar sin backup confirmado.**

---

## PASO 2 — EJECUTAR SQL (lo hace Gustavo manualmente)

Archivo: `legal/sql/01_parche_legal.sql`

> **Claude Code NO ejecuta este paso.** Debe indicarle a Gustavo que ejecute el SQL y esperar confirmación.

### Cómo ejecutar (Gustavo elige):

**Opción A: phpMyAdmin (cPanel) — Recomendada**
1. Ir a phpMyAdmin → seleccionar la BD del sitio
2. Pestaña "Importar" → seleccionar `01_parche_legal.sql` → "Ejecutar"

**Opción B: phpMyAdmin copiando SQL**
1. Abrir el archivo `01_parche_legal.sql` en un editor
2. Copiar todo el contenido
3. phpMyAdmin → pestaña "SQL" → pegar → "Ejecutar"

**Opción C: Desde SSH (si se prefiere)**
```bash
mysql -u [usuario] -p [nombre_bd] < /home/purranque/v2.regalos.purranque.info/legal/sql/01_parche_legal.sql
```

Esto crea 3 tablas nuevas:

### Tabla `consentimientos` (registro de cookies)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| session_id | VARCHAR(128) | ID de sesión del visitante |
| ip | VARCHAR(45) | IP del visitante |
| tipo | ENUM('cookies_esenciales','cookies_todas') | Qué aceptó |
| user_agent | VARCHAR(512) | Navegador |
| created_at | DATETIME | Fecha de aceptación |

### Tabla `solicitudes_arco` (formularios de derechos)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| tipo | ENUM('acceso','rectificacion','cancelacion','oposicion','portabilidad') | Derecho ejercido |
| nombre | VARCHAR(255) | Nombre del solicitante |
| email | VARCHAR(255) | Email para respuesta |
| rut | VARCHAR(12) | RUT (opcional) |
| descripcion | TEXT | Detalle + metadatos [COMERCIANTE:...] [MOTIVO:...] |
| estado | ENUM('recibida','en_proceso','resuelta','rechazada') | Estado actual |
| respuesta | TEXT | Respuesta del admin |
| ip | VARCHAR(45) | IP del solicitante |
| fecha_solicitud | DATETIME | Fecha de envío |
| fecha_respuesta | DATETIME | Fecha de resolución |
| fecha_limite | DATETIME (calculado) | +14 días calendario (~10 hábiles) |

### Tabla `registro_tratamiento` (Ley 21.719)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT AUTO_INCREMENT | PK |
| dato_personal | VARCHAR(255) | Ej: "Nombre", "Email", "Dirección IP" |
| fuente | ENUM('resena','contacto','registro_comercio','navegacion','cookies') | Origen |
| finalidad | VARCHAR(512) | Para qué se usa |
| base_legal | VARCHAR(255) | Fundamento legal |
| plazo_conservacion | VARCHAR(255) | Cuánto se guarda |
| medidas_seguridad | VARCHAR(512) | Cómo se protege |

El SQL también inserta 10 registros iniciales en `registro_tratamiento` con los datos que el sitio actualmente recopila.

### ALTER TABLE para reseñas (comentado)

Al final del SQL hay líneas comentadas para agregar moderación previa a la tabla de reseñas. **Verificar el nombre real de la tabla** antes de descomentar:

```sql
ALTER TABLE resenas ADD COLUMN moderado TINYINT(1) NOT NULL DEFAULT 0 AFTER estado;
ALTER TABLE resenas ADD COLUMN moderado_por INT DEFAULT NULL AFTER moderado;
ALTER TABLE resenas ADD COLUMN moderado_at DATETIME DEFAULT NULL AFTER moderado_por;
UPDATE resenas SET moderado = 1 WHERE estado = 'aprobada';
```

---

## PASO 3 — BANNER DE COOKIES

Archivo: `legal/templates/banner-cookies.html`

### Qué hacer:
1. Abrir el **layout principal** del sitio (el archivo que contiene `</body>`).
2. Pegar el contenido de `banner-cookies.html` **justo ANTES de `</body>`**.
3. El banner es **100% standalone**: incluye HTML + CSS + JS en un solo bloque. No requiere dependencias externas.

### Qué hace el banner:
- Aparece en la primera visita (sin cookie `rp_consent`).
- Ofrece 2 botones: "Aceptar todas" y "Solo esenciales".
- Al aceptar, guarda cookie `rp_consent` (365 días) y envía POST a `/api/consentimiento.php`.
- No vuelve a aparecer en visitas posteriores.

---

## PASO 4 — API DE CONSENTIMIENTO

Archivo: `legal/php/api_consentimiento.php`

### Qué hacer:
1. Copiar a la carpeta `/api/` del proyecto (o equivalente).
2. **Ajustar la conexión a BD**: reemplazar el bloque PDO con el `require`/`include` que usa el proyecto, o configurar las credenciales correctas (`$host`, `$dbname`, `$user`, `$pass`).
3. Verificar que la ruta `/api/consentimiento.php` sea accesible por POST.

### Qué hace:
- Recibe POST con `{tipo: "cookies_todas"}` o `{tipo: "cookies_esenciales"}`.
- Registra en tabla `consentimientos`: session_id, ip, tipo, user_agent.
- Retorna JSON `{ok: true}`.

---

## PASO 5 — PÁGINA /derechos (FORMULARIOS ARCO)

Archivos:
- `legal/php/pagina_derechos.php` — Controlador PHP completo
- `legal/templates/derechos.css` — Estilos CSS

### Qué hacer:
1. Copiar `pagina_derechos.php` a la carpeta de páginas del proyecto.
2. **Ajustar conexión a BD** (igual que en paso 4).
3. **Agregar ruta** `/derechos` en el router del proyecto que apunte a este archivo.
4. Agregar CSS: copiar contenido de `derechos.css` al final de la hoja de estilos principal, o crear archivo separado y cargarlo solo en `/derechos`.
5. Adaptar el archivo al layout del sitio (incluir header/footer si es necesario).

### Qué hace la página:
La página funciona en **2 pasos**:

**Paso 1 — Selección de derecho:**
El usuario ve 5 tarjetas clickeables + 4 accesos rápidos:

| # | Derecho | Icono | Descripción |
|---|---------|-------|-------------|
| 1 | Acceso | 🔍 | Quiero saber qué datos tienen sobre mí |
| 2 | Rectificación | ✏️ | Mis datos están incorrectos, quiero corregirlos |
| 3 | Cancelación / Baja | 🗑️ | Quiero eliminar mis datos y/o mi comercio |
| 4 | Oposición | 🚫 | No quiero que usen mis datos para cierta finalidad |
| 5 | Portabilidad | 📦 | Quiero recibir copia de mis datos en formato digital |

Accesos rápidos (casos frecuentes):
- "Soy comerciante y quiero eliminar mi negocio" → Cancelación
- "Quiero eliminar una reseña que publiqué" → Cancelación
- "Quiero saber qué datos tienen sobre mí" → Acceso
- "Los datos de mi comercio están incorrectos" → Rectificación

**Paso 2 — Formulario específico:**
Al hacer clic, se abre el formulario adaptado al derecho seleccionado.

### Formulario de Cancelación / Darse de Baja (el más completo):

Campos específicos:
- **Checkbox "Soy comerciante registrado"** → Si marca, aparece campo "Nombre de tu comercio en la plataforma"
- **Selector "Motivo de la solicitud"** con opciones:
  - Mi comercio cerró o ya no opera
  - No autoricé la publicación de mis datos
  - Prefiero usar otra plataforma
  - Razones de privacidad personal
  - Otro motivo
- **Checkboxes "¿Qué deseas eliminar?"**:
  - ☐ Mi comercio y toda su información (ficha, fotos, datos de contacto)
  - ☐ Las reseñas que he publicado en otros comercios
  - ☐ Mi cuenta completa y todos los datos asociados
  - ☐ Otro (especificar en la descripción)

Campos comunes (todos los formularios):
- Nombre completo (obligatorio, mín 3 caracteres)
- Email (obligatorio, formato válido)
- RUT (opcional)
- Teléfono de contacto (opcional)
- Descripción detallada (obligatorio, mín 10 / máx 5.000 caracteres)

### Seguridad del formulario:
- **Rate limiting:** máximo 3 solicitudes por email en 24 horas.
- **Validación server-side:** campos obligatorios, formato email, largo de texto.
- **Metadatos en descripción:** se agregan automáticamente `[COMERCIANTE: nombre]` y `[MOTIVO: texto]` al inicio.

### Emails automáticos:

**Email al admin (contacto@purranque.info):**
- Asunto: "Solicitud ARCO #[ID] — [Tipo]"
- Contenido: ID, tipo, nombre, email, RUT, teléfono, si es comerciante, nombre comercio, motivo baja, descripción, fecha límite.

**Email al solicitante:**
- Asunto: "Solicitud recibida #[ID] — Regalos Purranque"
- Contenido: confirmación, número de seguimiento, plazo 10 días hábiles, contacto alternativo.

### URLs directas:
Los formularios aceptan parámetro GET para abrir directamente un tipo:
- `/derechos?tipo=cancelacion` → abre formulario de baja
- `/derechos?tipo=acceso` → abre formulario de consulta
- `/derechos?tipo=rectificacion` → abre formulario de corrección

---

## PASO 6 — HEADERS DE SEGURIDAD (Apache / .htaccess)

Archivo de referencia: `legal/templates/nginx-security-headers.conf`

> **IMPORTANTE:** HostGator usa **Apache**, no Nginx. Los headers se agregan en `.htaccess`, NO en archivos de configuración de Nginx.

### Qué hacer:
1. Abrir (o crear) el archivo **`.htaccess`** en la carpeta raíz del sitio.
2. Agregar las siguientes líneas al inicio del archivo (antes de cualquier RewriteRule existente):

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

3. **No tocar** el resto del `.htaccess` (puede tener reglas de rewrite, PHP settings, etc.).
4. Verificar que el sitio sigue funcionando después de guardar.

### Verificar:
```bash
curl -I https://v2.regalos.purranque.info 2>/dev/null | grep -i 'x-frame\|x-content\|referrer'
```
Debe mostrar los 3 headers. Si no aparecen, verificar que `mod_headers` está habilitado en HostGator (normalmente sí lo está).

---

## PASO 7 — ACTUALIZAR TEXTOS LEGALES

Archivo: `legal/textos-legales/clausulas-nuevas.txt`

Contiene 8 bloques de texto. Cada uno indica **exactamente dónde** se agrega:

### En /terminos (Términos y Condiciones):

| Bloque | Qué agregar | Dónde |
|--------|-------------|-------|
| 1 | **Sección 6.1 — Indemnización** | Después de sección 6 |
| 2 | **Sección 6.2 — Licencia de Contenido** | Después de nueva 6.1 |
| 3 | **Sección 2.1 — Estado BETA** | Después de sección 2 |

**Sección 6.1 Indemnización** — El comerciante se compromete a mantener indemne a Regalos Purranque frente a reclamos derivados de: información publicada por el comerciante, incumplimiento de términos, y vulneración de derechos de terceros.

**Sección 6.2 Licencia de Contenido** — Al subir contenido (fotos, logos, descripciones), el comerciante otorga licencia no exclusiva, gratuita, mundial e indefinida para mostrar en la plataforma, promoción, y adaptación de formato. No implica transferencia de propiedad intelectual. Puede solicitar eliminación en contacto@purranque.info.

**Sección 2.1 Estado BETA** — Aviso de que la plataforma está en etapa de validación territorial (BETA): funcionalidades incompletas, posibles errores, disponibilidad no garantizada al 100%.

### En /privacidad (Política de Privacidad):

| Bloque | Qué agregar | Dónde |
|--------|-------------|-------|
| 4 | **Sección 6 — Derechos del Titular** (REEMPLAZO COMPLETO) | Reemplazar sección 6 existente |
| 5 | **Sección 6.1 — Registro de Actividades de Tratamiento** | Después de nueva sección 6 |
| 6 | **Sección 12.1 — Delegado de Protección de Datos** | Antes de sección de Contacto |
| 7 | **Enlace /derechos en footer** | Sección Legal del footer |
| 8 | **Referencia Ley 21.719 en sección 1** | Reemplazar texto parcial |

**Sección 6 nueva** — Detalla los 5 derechos ARCO (Acceso, Rectificación, Cancelación, Oposición, Portabilidad) con referencia a Ley 19.628 modificada por Ley 21.719. Indica 2 formas de ejercerlos: formulario web en /derechos y correo a contacto@purranque.info. Plazo: 10 días hábiles.

**Sección 6.1 Registro de Tratamiento** — Declara que se mantiene registro interno de actividades de tratamiento (tipos de datos, finalidad, base legal, plazos, medidas de seguridad). Disponible para la autoridad de protección de datos.

**Sección 12.1 DPD** — Delegado de Protección de Datos: Gustavo Sepúlveda Sánchez, contacto@purranque.info, Purranque, Región de Los Lagos, Chile.

**Referencia Ley 21.719** — En sección 1 de Privacidad, donde dice "Ley 19.628 sobre Protección de la Vida Privada", cambiar a "Ley 19.628 sobre Protección de la Vida Privada, **modificada por la Ley 21.719 sobre Protección de Datos Personales**".

### En el footer:

Agregar en la sección Legal (después de "Política de Contenidos"):
```html
<a href="/derechos">Ejercicio de Derechos</a>
```

---

## PASO 8 — VERIFICACIÓN

Ejecutar estos checks después de implementar todo:

### Verificaciones de BD (Gustavo en phpMyAdmin):
- [ ] Tabla `consentimientos` existe
- [ ] Tabla `solicitudes_arco` existe
- [ ] Tabla `registro_tratamiento` existe con 10 registros

### Verificaciones técnicas (desde terminal o navegador):
```bash
# 1. Verificar headers de seguridad
curl -I https://v2.regalos.purranque.info 2>/dev/null | grep -i 'x-frame\|x-content\|referrer'
# Debe mostrar X-Frame-Options, X-Content-Type-Options, Referrer-Policy

# 2. Verificar que /derechos responde
curl -s -o /dev/null -w "%{http_code}" https://v2.regalos.purranque.info/derechos
# Debe dar 200

# 3. Verificar que el banner de cookies está en el HTML
curl -s https://v2.regalos.purranque.info | grep -c "rp-cookie-banner"
# Debe dar >= 1
```

Verificaciones manuales (en navegador):
- [ ] Visitar https://v2.regalos.purranque.info en modo incógnito → debe aparecer banner de cookies
- [ ] Aceptar cookies → banner desaparece, no vuelve al recargar
- [ ] Visitar https://v2.regalos.purranque.info/derechos → debe mostrar 5 tarjetas + 4 casos frecuentes
- [ ] Click en "Eliminar mis datos" → formulario con checkboxes de qué eliminar
- [ ] Marcar "Soy comerciante" → aparece campo nombre del comercio
- [ ] Enviar solicitud de prueba → debe llegar email a contacto@purranque.info
- [ ] Verificar en BD (phpMyAdmin): `SELECT * FROM solicitudes_arco;` → debe tener la solicitud
- [ ] Verificar /terminos tiene secciones 2.1, 6.1, 6.2
- [ ] Verificar /privacidad referencia Ley 21.719 y tiene DPD
- [ ] Verificar footer incluye enlace "Ejercicio de Derechos"

---

## Archivos de Referencia Local

En la máquina local de desarrollo, el proyecto se encuentra en:

```
C:\Proyectos\regalospurranque.cl\
```

Dentro de esa carpeta existe una subcarpeta `legal\` con documentos legales, auditorías y material de referencia:

```
C:\Proyectos\regalospurranque.cl\legal\
```

Esta carpeta contiene: auditorías previas, borradores de textos legales, los archivos del paquete del parche, documentación del proyecto PurranQUE.INFO, y otros recursos. **Todo el contenido de esta carpeta se sube al servidor de HostGator** dentro de la carpeta raíz del sitio v2.regalos.purranque.info, manteniendo la misma estructura de subcarpetas.

### Flujo de trabajo:
1. Gustavo prepara los archivos en `C:\Proyectos\regalospurranque.cl\legal\` (local)
2. Sube la carpeta `legal/` completa al servidor de HostGator (vía cPanel, FTP, o SSH)
3. Claude Code lee los archivos desde `legal/` en el servidor y ejecuta la integración

---

## Archivos del Paquete (carpeta legal/ dentro del sitio)

Todos los archivos se suben a una carpeta llamada `legal/` en la raíz del sitio v2.regalos.purranque.info en HostGator:

```
/home/purranque/v2.regalos.purranque.info/
├── legal/                                ← CARPETA NUEVA con todo el paquete
│   ├── sql/
│   │   └── 01_parche_legal.sql           → 3 tablas + datos iniciales + ALTER TABLE reseñas
│   ├── php/
│   │   ├── api_consentimiento.php        → POST /api/consentimiento (registro cookies)
│   │   └── pagina_derechos.php           → Página /derechos (formularios ARCO completos)
│   ├── templates/
│   │   ├── banner-cookies.html           → Banner cookies (HTML+CSS+JS standalone)
│   │   ├── derechos.css                  → Estilos para /derechos
│   │   └── nginx-security-headers.conf   → Headers (REFERENCIA, usar .htaccess en su lugar)
│   └── textos-legales/
│       └── clausulas-nuevas.txt          → 8 bloques de texto para /terminos y /privacidad
├── index.php                             ← Archivo principal del sitio (ya existe)
├── .htaccess                             ← Agregar headers de seguridad aquí
└── ... (resto del sitio existente)
```

> **IMPORTANTE:** Los archivos en `legal/` son archivos FUENTE. No se ejecutan directamente desde ahí. Claude Code debe **copiar e integrar** su contenido en las ubicaciones correctas del sitio (api/, pages/, layout, footer, etc.).

## Reglas

- **NUNCA incluir credenciales** (BD, passwords, API keys) en archivos del repo. Es público.
- **NUNCA ejecutar comandos mysql** directamente. La BD la gestiona Gustavo vía phpMyAdmin.
- **Explorar primero, modificar después.** Entender la estructura antes de tocar archivos.
- **Adaptar los archivos del paquete a la estructura existente**, no al revés.
- **Reutilizar el patrón de conexión a BD** que ya usa el proyecto (require/include a archivo local no versionado).
- **No instalar dependencias** (Composer, npm, etc.). HostGator compartido no lo permite fácilmente.
- **No usar comandos sudo ni systemctl.** Es hosting compartido, no hay acceso root.
- **Headers van en .htaccess**, no en configuración de Nginx.
- **Los archivos fuente están en `legal/`** dentro de la carpeta raíz del sitio. Copiar e integrar desde ahí.
- **Confirmar cada paso** antes de pasar al siguiente.
- **Si algo falla, restaurar el backup** y reportar el error.
- **URL de verificación:** https://v2.regalos.purranque.info
- **Al terminar:** `git add . && git commit -m "Parche legal Feb 2026" && git push`

## Contacto

- **Responsable:** Gustavo Sepúlveda Sánchez
- **Email:** contacto@purranque.info
- **Proyecto:** PurranQUE.INFO
- **Ubicación:** Purranque, Región de Los Lagos, Chile

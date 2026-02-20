# Prompt para Claude Code — Mejoras UX/UI (Auditoría C.5)

Lee `legal/PARCHE-LEGAL.md` para entender el flujo de trabajo y restricciones del proyecto.

## Contexto

El sitio regalospurranque.cl / v2.regalos.purranque.info es un directorio comercial con muy pocos comercios registrados (actualmente ~2). La auditoría UX/UI detectó que la percepción de "plataforma vacía" es el riesgo más grave: el usuario ve categorías con 0 comercios, fechas con 0 comercios, y se va. Necesitamos corregir esto.

## Tareas a ejecutar (en orden de prioridad)

---

### 1. OCULTAR CATEGORÍAS CON 0 COMERCIOS [CRÍTICO]

La home muestra 10 categorías. De esas 10, aproximadamente 7 muestran 0 comercios. Esto transmite "plataforma vacía".

**Qué hacer:**
- Buscar dónde se renderizan las categorías en la home (probablemente en `views/` o en un controller en `app/`).
- Modificar la query o la lógica para que **solo se muestren categorías que tengan al menos 1 comercio activo/publicado**.
- La query debe hacer un JOIN o subquery con la tabla de comercios y filtrar por `COUNT > 0`.
- Las categorías ocultas se habilitarán automáticamente cuando se registre un comercio en ellas.
- **NO eliminar** las categorías de la BD, solo ocultarlas en la vista pública.

---

### 2. OCULTAR FECHAS/CELEBRACIONES CON 0 COMERCIOS [CRÍTICO]

La home muestra 9 celebraciones personales, 12 fechas calendario, y 5 eventos comerciales. De ~26 en total, ~22 muestran 0 comercios.

**Qué hacer:**
- Buscar dónde se renderizan las celebraciones y fechas en la home.
- Aplicar la misma lógica: **solo mostrar fechas/celebraciones que tengan al menos 1 comercio asociado**.
- Si después del filtro no queda ninguna fecha, **ocultar la sección completa** (no mostrar un título con contenido vacío debajo).
- Aplicar esto a las 3 secciones: celebraciones personales, fechas calendario, eventos comerciales.

---

### 3. COUNTDOWN INTELIGENTE [CRÍTICO]

El hero tiene un countdown que apunta a una fecha (ej: Día de la Mujer) pero esa fecha tiene 0 comercios. Genera expectativa que no se cumple.

**Qué hacer:**
- Buscar dónde se configura el countdown (puede estar hardcodeado en una vista o en config).
- Modificar la lógica para que el countdown **solo apunte a la próxima fecha que TENGA comercios asociados**.
- La lógica debe:
  1. Consultar las fechas futuras que tienen al menos 1 comercio asociado.
  2. Ordenar por fecha ascendente.
  3. Tomar la primera (la más próxima).
  4. Si no hay ninguna fecha con comercios → **no mostrar countdown**. En su lugar, mostrar el hero con el buscador o con los comercios destacados.
- El countdown debe actualizarse automáticamente: cuando una fecha pasa, apunta a la siguiente fecha con comercios.

---

### 4. SECCIONES VACÍAS: OCULTAR AUTOMÁTICAMENTE [ALTO]

Regla general para toda la home y páginas de listado:

**Qué hacer:**
- Revisar TODAS las secciones de la home que muestran listados dinámicos.
- Implementar la regla: **si una sección tiene 0 items después del filtro, no renderizar la sección** (ni título, ni contenedor, ni "No hay resultados").
- Esto aplica a: categorías, celebraciones, fechas, eventos comerciales, comercios destacados, noticias.
- Si la sección de "Comercios destacados" tiene 0 destacados, no mostrarla.
- Si la sección de "Noticias" tiene 0 noticias, no mostrarla.

---

### 5. CONTENIDO MÍNIMO PARA FICHAS [MEDIO]

Las fichas de comercios tienen contenido muy escueto (1 línea de descripción). Necesitamos un validador de calidad mínima.

**Qué hacer:**
- Buscar dónde se crea/edita una ficha de comercio (panel admin o formulario).
- Agregar validación de contenido mínimo antes de que una ficha sea visible públicamente:
  - Descripción: mínimo 100 caracteres (aprox. 3 líneas)
  - Al menos 1 imagen (portada)
  - Al menos 1 dato de contacto (WhatsApp, email, o teléfono)
- Las fichas que no cumplan el mínimo se guardan como "borrador" (no visibles públicamente).
- Mostrar al comerciante un indicador de completitud: "Tu ficha está al 40% — agrega más fotos y descripción para publicarla".
- Si la validación se implementa en backend, agregar una columna `calidad_ok` o similar (indicarme el SQL para ejecutar en phpMyAdmin).

---

### 6. HOME MOBILE: PRIORIZAR CONTENIDO [MEDIO]

En mobile, la home tiene demasiado scroll (6 secciones). El usuario debe scrollear mucho para llegar a los comercios.

**Qué hacer:**
- Reorganizar el orden de secciones en mobile (usando CSS `order` o clases condicionales):
  1. **Buscador** (siempre primero)
  2. **Categorías con comercios** (las que pasen el filtro del punto 1)
  3. **Comercios destacados**
  4. **Próximo evento** (si hay countdown)
  5. **Noticias** (si hay)
- Las celebraciones y fechas calendario **no se muestran en mobile** en la home. Agregar un enlace "Ver todas las fechas y celebraciones →" que lleve a una página separada (`/celebraciones` o `/fechas`).
- Usar `display: none` en mobile para las secciones que se ocultan, o mejor aún, clases CSS tipo:
  ```css
  @media (max-width: 768px) {
    .section-celebraciones-home,
    .section-fechas-home {
      display: none;
    }
  }
  ```
- Agregar enlace de "Ver más" visible solo en mobile para acceder a esas secciones.

---

### 7. HERO SIN COUNTDOWN: ALTERNATIVA [BAJO]

Si no hay fecha próxima con comercios (punto 3), el hero queda sin countdown.

**Qué hacer:**
- Crear una versión alternativa del hero que se muestre cuando no hay countdown:
  - Opción A: Hero con buscador prominente + texto "Encuentra comercios en Purranque"
  - Opción B: Hero con los comercios destacados en formato carousel/cards
- La decisión de qué hero mostrar debe ser automática (basada en si hay fecha próxima con comercios o no).

---

## Restricciones (recordatorio)

- **Repo público:** No incluir credenciales en archivos del repo
- **BD la gestiona Gustavo:** No ejecutar mysql. Generar SQL y decirme qué ejecutar en phpMyAdmin
- **Apache/.htaccess:** No es Nginx
- **No tocar CLAUDE.md** del proyecto
- **Al terminar:** git add, commit, push y avisarme para deploy en cPanel

## Orden de prioridad

1. 🔴 Ocultar categorías con 0 comercios (CRÍTICO)
2. 🔴 Ocultar fechas/celebraciones con 0 comercios (CRÍTICO)
3. 🔴 Countdown inteligente (CRÍTICO)
4. 🟠 Secciones vacías: ocultar automáticamente (ALTO)
5. 🟡 Contenido mínimo para fichas (MEDIO)
6. 🟡 Home mobile: priorizar contenido (MEDIO)
7. 🟢 Hero alternativo sin countdown (BAJO)

Empieza explorando la estructura del proyecto para entender: cómo se renderizan las categorías, dónde están las queries de la home, cómo funciona el countdown, y qué tablas/relaciones existen entre comercios, categorías, y fechas. Luego ejecuta las tareas en orden.

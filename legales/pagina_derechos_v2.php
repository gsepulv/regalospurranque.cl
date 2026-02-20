<?php
/**
 * Controlador: Ejercicio de Derechos sobre Datos Personales
 * Ruta: /derechos
 * Versión: 2.0 — Incluye formularios específicos por caso de uso
 * 
 * INTEGRACIÓN:
 * 1. Copiar a tu carpeta de controladores/páginas
 * 2. Agregar ruta en tu router: '/derechos' => 'derechos.php'
 * 3. Ajustar la conexión a BD según tu estructura
 * 4. Ajustar la función de envío de email según tu método actual
 */

// --- CONEXIÓN BD (ajustar según tu estructura) ---
// require_once __DIR__ . '/../includes/db.php';

$host = 'localhost';
$dbname = 'regalospurranque';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error de conexión');
}
// --- FIN CONEXIÓN ---

$mensaje_exito = '';
$mensaje_error = '';
$errores = [];

// Tipos de solicitud con metadata
$tipos_config = [
    'acceso' => [
        'icono' => '🔍',
        'titulo' => 'Consultar mis datos',
        'desc' => 'Quiero saber qué datos personales tienen almacenados sobre mí',
        'placeholder' => 'Describe qué información deseas conocer. Por ejemplo: "Quiero saber qué datos tienen asociados a mi email" o "Necesito saber qué información de mi comercio está almacenada".',
        'campos_extra' => [],
    ],
    'rectificacion' => [
        'icono' => '✏️',
        'titulo' => 'Corregir mis datos',
        'desc' => 'Mis datos están incorrectos o incompletos y quiero corregirlos',
        'placeholder' => 'Indica qué datos son incorrectos y cuál es la información correcta. Por ejemplo: "Mi dirección aparece como Av. Chile 100, pero la correcta es Av. Chile 200" o "Mi nombre de comercio cambió a...".',
        'campos_extra' => ['nombre_comercio'],
    ],
    'cancelacion' => [
        'icono' => '🗑️',
        'titulo' => 'Eliminar mis datos / Darme de baja',
        'desc' => 'Quiero que eliminen mis datos personales y/o mi comercio de la plataforma',
        'placeholder' => 'Indica qué deseas eliminar. Por ejemplo: "Quiero eliminar mi comercio y todos los datos asociados" o "Quiero que borren mi reseña publicada en el comercio X" o "Quiero eliminar mi cuenta completa".',
        'campos_extra' => ['nombre_comercio', 'motivo_baja'],
    ],
    'oposicion' => [
        'icono' => '🚫',
        'titulo' => 'Oponerme al uso de mis datos',
        'desc' => 'No quiero que traten mis datos para cierta finalidad',
        'placeholder' => 'Indica a qué tratamiento te opones. Por ejemplo: "No quiero que mis datos aparezcan en el mapa" o "No quiero que mi email sea utilizado para comunicaciones".',
        'campos_extra' => [],
    ],
    'portabilidad' => [
        'icono' => '📦',
        'titulo' => 'Recibir copia de mis datos',
        'desc' => 'Quiero recibir mis datos en formato digital descargable',
        'placeholder' => 'Indica qué datos necesitas recibir. Te los enviaremos en formato CSV o JSON al email proporcionado.',
        'campos_extra' => [],
    ],
];

$motivos_baja = [
    'cierre_negocio' => 'Mi comercio cerró o ya no opera',
    'no_autorizo' => 'No autoricé la publicación de mis datos',
    'cambio_plataforma' => 'Prefiero usar otra plataforma',
    'privacidad' => 'Razones de privacidad personal',
    'otro' => 'Otro motivo',
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo        = trim($_POST['tipo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $rut         = trim($_POST['rut'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $nombre_comercio = trim($_POST['nombre_comercio'] ?? '');
    $motivo_baja = trim($_POST['motivo_baja'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $es_comerciante = isset($_POST['es_comerciante']) ? 1 : 0;

    if (!array_key_exists($tipo, $tipos_config)) {
        $errores[] = 'Selecciona un tipo de solicitud válido.';
    }
    if (empty($nombre) || strlen($nombre) < 3) {
        $errores[] = 'El nombre es obligatorio (mínimo 3 caracteres).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (empty($descripcion) || strlen($descripcion) < 10) {
        $errores[] = 'La descripción debe tener al menos 10 caracteres.';
    }
    if (strlen($descripcion) > 5000) {
        $errores[] = 'La descripción no puede superar los 5.000 caracteres.';
    }

    // Rate limiting: máx 3 solicitudes por email en 24h
    if (empty($errores)) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM solicitudes_arco 
             WHERE email = :email AND fecha_solicitud > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() >= 3) {
            $errores[] = 'Has enviado demasiadas solicitudes en las últimas 24 horas. Intenta mañana.';
        }
    }

    if (empty($errores)) {
        // Construir descripcion completa con metadatos
        $desc_completa = $descripcion;
        if ($es_comerciante && !empty($nombre_comercio)) {
            $desc_completa = "[COMERCIANTE: {$nombre_comercio}] " . $desc_completa;
        }
        if ($tipo === 'cancelacion' && !empty($motivo_baja)) {
            $motivo_texto = $motivos_baja[$motivo_baja] ?? $motivo_baja;
            $desc_completa = "[MOTIVO: {$motivo_texto}] " . $desc_completa;
        }
        if (!empty($telefono)) {
            $desc_completa .= "\n[TELÉFONO CONTACTO: {$telefono}]";
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO solicitudes_arco (tipo, nombre, email, rut, descripcion, ip) 
                 VALUES (:tipo, :nombre, :email, :rut, :descripcion, :ip)"
            );
            $stmt->execute([
                ':tipo'        => $tipo,
                ':nombre'      => $nombre,
                ':email'       => $email,
                ':rut'         => !empty($rut) ? $rut : null,
                ':descripcion' => $desc_completa,
                ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]);

            $id_solicitud = $pdo->lastInsertId();

            // Determinar texto descriptivo del tipo
            $tipo_texto = $tipos_config[$tipo]['titulo'] ?? $tipo;

            // Email al admin
            $asunto_admin = "Solicitud ARCO #{$id_solicitud} — {$tipo_texto}";
            $cuerpo_admin = "Nueva solicitud de derechos ARCO:\n\n"
                . "ID: #{$id_solicitud}\n"
                . "Tipo: {$tipo_texto}\n"
                . "Nombre: {$nombre}\n"
                . "Email: {$email}\n"
                . "RUT: " . ($rut ?: 'No informado') . "\n"
                . "Teléfono: " . ($telefono ?: 'No informado') . "\n"
                . "Comerciante: " . ($es_comerciante ? "Sí — {$nombre_comercio}" : "No") . "\n"
                . ($tipo === 'cancelacion' ? "Motivo de baja: " . ($motivos_baja[$motivo_baja] ?? 'No especificado') . "\n" : "")
                . "\nDescripción:\n{$descripcion}\n\n"
                . "⏰ Plazo de respuesta: 10 días hábiles (vence: " . date('d/m/Y', strtotime('+14 days')) . ")\n"
                . "Gestionar en el panel de administración.";
            @mail('contacto@purranque.info', $asunto_admin, $cuerpo_admin,
                "From: noreply@regalospurranque.cl\r\nContent-Type: text/plain; charset=UTF-8");

            // Email de confirmación al solicitante
            $asunto_user = "Solicitud recibida #{$id_solicitud} — Regalos Purranque";
            $cuerpo_user = "Estimado/a {$nombre},\n\n"
                . "Hemos recibido su solicitud de ejercicio de derechos.\n\n"
                . "📋 Detalle de su solicitud:\n"
                . "   Tipo: {$tipo_texto}\n"
                . "   Número de seguimiento: #{$id_solicitud}\n"
                . "   Fecha: " . date('d/m/Y H:i') . "\n\n"
                . "⏰ Plazo de respuesta: 10 días hábiles.\n\n"
                . "Le responderemos al email proporcionado dentro del plazo legal.\n"
                . "Si necesita comunicarse antes, puede escribirnos a contacto@purranque.info\n\n"
                . "Atentamente,\n"
                . "Regalos Purranque\n"
                . "Un proyecto de PurranQUE.INFO";
            @mail($email, $asunto_user, $cuerpo_user,
                "From: contacto@purranque.info\r\nContent-Type: text/plain; charset=UTF-8");

            $mensaje_exito = "Solicitud #{$id_solicitud} recibida correctamente. Recibirás confirmación en tu email. Plazo de respuesta: 10 días hábiles.";

        } catch (PDOException $e) {
            $mensaje_error = 'Error al procesar la solicitud. Intenta nuevamente o escríbenos a contacto@purranque.info';
        }
    }
}

// Tipo seleccionado (por GET para enlaces directos o POST para errores)
$tipo_seleccionado = $_GET['tipo'] ?? $_POST['tipo'] ?? '';
?>

<!-- Breadcrumbs -->
<nav class="breadcrumbs" aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Inicio</a></li>
    <li aria-current="page">Ejercicio de Derechos</li>
  </ol>
</nav>

<div class="page-content derechos-page">

  <h1>Ejercicio de Derechos sobre Datos Personales</h1>

  <div class="derechos-intro">
    <p>
      En cumplimiento de la <strong>Ley 19.628</strong> y la <strong>Ley 21.719</strong> sobre
      Protección de Datos Personales, puedes ejercer tus derechos ARCO
      (Acceso, Rectificación, Cancelación, Oposición) y de Portabilidad.
    </p>
    <p>
      <strong>Plazo de respuesta:</strong> 10 días hábiles desde la recepción.<br>
      <strong>Contacto alternativo:</strong> <a href="mailto:contacto@purranque.info">contacto@purranque.info</a>
    </p>
  </div>

  <?php if (!empty($mensaje_exito)): ?>
    <div class="alert alert-success">
      <span class="alert-icon">✅</span>
      <div>
        <p><strong>Solicitud enviada correctamente</strong></p>
        <p><?= htmlspecialchars($mensaje_exito) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($mensaje_error)): ?>
    <div class="alert alert-error">
      <span class="alert-icon">❌</span>
      <p><?= htmlspecialchars($mensaje_error) ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-error">
      <span class="alert-icon">⚠️</span>
      <ul>
        <?php foreach ($errores as $err): ?>
          <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- PASO 1: Elegir qué derecho ejercer (cards clickeables)       -->
  <!-- ============================================================ -->
  <?php if (empty($mensaje_exito) && empty($tipo_seleccionado)): ?>

  <h2>¿Qué necesitas hacer?</h2>
  <p>Selecciona la opción que mejor describe tu solicitud:</p>

  <div class="derechos-grid">
    <?php foreach ($tipos_config as $key => $config): ?>
      <a href="/derechos?tipo=<?= $key ?>" class="derecho-card derecho-card--clickable">
        <span class="derecho-icon"><?= $config['icono'] ?></span>
        <h3><?= htmlspecialchars($config['titulo']) ?></h3>
        <p><?= htmlspecialchars($config['desc']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Accesos rápidos para casos comunes -->
  <div class="casos-comunes">
    <h3>Casos frecuentes</h3>
    <div class="casos-grid">
      <a href="/derechos?tipo=cancelacion" class="caso-card">
        <span>🏪</span>
        <strong>Soy comerciante y quiero eliminar mi negocio de la plataforma</strong>
      </a>
      <a href="/derechos?tipo=cancelacion" class="caso-card">
        <span>💬</span>
        <strong>Quiero eliminar una reseña que publiqué</strong>
      </a>
      <a href="/derechos?tipo=acceso" class="caso-card">
        <span>🔎</span>
        <strong>Quiero saber qué datos tienen sobre mí</strong>
      </a>
      <a href="/derechos?tipo=rectificacion" class="caso-card">
        <span>📝</span>
        <strong>Los datos de mi comercio están incorrectos</strong>
      </a>
    </div>
  </div>

  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- PASO 2: Formulario específico según tipo seleccionado        -->
  <!-- ============================================================ -->
  <?php if (empty($mensaje_exito) && !empty($tipo_seleccionado) && isset($tipos_config[$tipo_seleccionado])): ?>
  <?php $cfg = $tipos_config[$tipo_seleccionado]; ?>

  <div class="form-header">
    <a href="/derechos" class="btn-volver">← Volver a opciones</a>
    <div class="form-header-title">
      <span class="form-header-icon"><?= $cfg['icono'] ?></span>
      <div>
        <h2><?= htmlspecialchars($cfg['titulo']) ?></h2>
        <p><?= htmlspecialchars($cfg['desc']) ?></p>
      </div>
    </div>
  </div>

  <form method="POST" action="/derechos" class="form-derechos" novalidate>
    <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo_seleccionado) ?>">

    <!-- ¿Eres comerciante registrado? -->
    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="es_comerciante" id="es_comerciante" value="1"
               <?= !empty($es_comerciante) ? 'checked' : '' ?>
               onchange="document.getElementById('campos-comerciante').style.display = this.checked ? 'block' : 'none'">
        <span>Soy comerciante registrado en Regalos Purranque</span>
      </label>
    </div>

    <!-- Campos extra para comerciante -->
    <div id="campos-comerciante" style="display: <?= !empty($es_comerciante) ? 'block' : 'none' ?>;">
      <div class="form-group">
        <label for="nombre_comercio">Nombre de tu comercio en la plataforma</label>
        <input type="text" name="nombre_comercio" id="nombre_comercio" maxlength="255"
               value="<?= htmlspecialchars($nombre_comercio ?? '') ?>"
               placeholder="Ej: Flores Las Camelias, Panadería Don Pedro...">
      </div>
    </div>

    <!-- Campos comunes -->
    <div class="form-row">
      <div class="form-group">
        <label for="nombre">Nombre completo <span class="required">*</span></label>
        <input type="text" name="nombre" id="nombre" required minlength="3" maxlength="255"
               value="<?= htmlspecialchars($nombre ?? '') ?>"
               placeholder="Tu nombre completo">
      </div>

      <div class="form-group">
        <label for="email">Correo electrónico <span class="required">*</span></label>
        <input type="email" name="email" id="email" required maxlength="255"
               value="<?= htmlspecialchars($email ?? '') ?>"
               placeholder="tu@email.com">
        <small class="form-help">Recibirás la respuesta en este correo.</small>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="rut">RUT (opcional)</label>
        <input type="text" name="rut" id="rut" maxlength="12"
               value="<?= htmlspecialchars($rut ?? '') ?>"
               placeholder="12.345.678-9">
        <small class="form-help">Ayuda a identificarte más rápidamente.</small>
      </div>

      <div class="form-group">
        <label for="telefono">Teléfono de contacto (opcional)</label>
        <input type="tel" name="telefono" id="telefono" maxlength="15"
               value="<?= htmlspecialchars($telefono ?? '') ?>"
               placeholder="+56 9 1234 5678">
        <small class="form-help">En caso de necesitar contactarte por otra vía.</small>
      </div>
    </div>

    <!-- Motivo de baja (solo para cancelación) -->
    <?php if ($tipo_seleccionado === 'cancelacion'): ?>
    <div class="form-group">
      <label for="motivo_baja">Motivo de la solicitud</label>
      <select name="motivo_baja" id="motivo_baja">
        <option value="">— Selecciona un motivo (opcional) —</option>
        <?php foreach ($motivos_baja as $val => $label): ?>
          <option value="<?= $val ?>" <?= (isset($motivo_baja) && $motivo_baja === $val) ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Qué eliminar específicamente -->
    <div class="form-group">
      <label>¿Qué deseas eliminar?</label>
      <div class="checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" name="eliminar_comercio" value="1">
          <span>Mi comercio y toda su información (ficha, fotos, datos de contacto)</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="eliminar_resenas" value="1">
          <span>Las reseñas que he publicado en otros comercios</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="eliminar_cuenta" value="1">
          <span>Mi cuenta completa y todos los datos asociados</span>
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="eliminar_otro" value="1">
          <span>Otro (especificar en la descripción)</span>
        </label>
      </div>
    </div>
    <?php endif; ?>

    <!-- Descripción -->
    <div class="form-group">
      <label for="descripcion">Descripción de tu solicitud <span class="required">*</span></label>
      <textarea name="descripcion" id="descripcion" required
                minlength="10" maxlength="5000" rows="5"
                placeholder="<?= htmlspecialchars($cfg['placeholder']) ?>"><?= htmlspecialchars($descripcion ?? '') ?></textarea>
      <small class="form-help char-count">
        <span id="char-count">0</span> / 5.000 caracteres
      </small>
    </div>

    <!-- Aviso legal -->
    <div class="form-legal-notice">
      <p>
        📋 Al enviar este formulario aceptas que tus datos (nombre, email, descripción)
        sean tratados exclusivamente para gestionar tu solicitud, conforme a nuestra
        <a href="/privacidad">Política de Privacidad</a>.
        Tus datos serán conservados hasta la resolución de la solicitud.
      </p>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">
        📨 Enviar solicitud
      </button>
      <a href="/derechos" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- Información complementaria (siempre visible)                 -->
  <!-- ============================================================ -->
  <div class="derechos-info">
    <h2>¿Qué son los derechos ARCO?</h2>
    <p>La ley chilena de protección de datos personales te garantiza estos derechos:</p>

    <div class="derechos-explicacion">
      <?php foreach ($tipos_config as $key => $config): ?>
      <div class="derecho-explica">
        <span class="derecho-explica-icon"><?= $config['icono'] ?></span>
        <div>
          <h3><?= $config['titulo'] ?></h3>
          <p><?= $config['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="derechos-legal-ref">
      <p>
        <strong>Base legal:</strong> Ley 19.628 sobre Protección de la Vida Privada,
        modificada por Ley 21.719 sobre Protección de Datos Personales.<br>
        <strong>Responsable:</strong> Regalos Purranque — PurranQUE.INFO<br>
        <strong>Delegado de Protección de Datos:</strong> Gustavo Sepúlveda Sánchez<br>
        <strong>Contacto:</strong> <a href="mailto:contacto@purranque.info">contacto@purranque.info</a><br>
        <strong>Jurisdicción:</strong> Tribunales de Osorno, Región de Los Lagos, Chile.
      </p>
    </div>
  </div>
</div>

<script>
// Contador de caracteres
(function() {
  var ta = document.getElementById('descripcion');
  var ct = document.getElementById('char-count');
  if (ta && ct) {
    function u() { ct.textContent = ta.value.length; }
    ta.addEventListener('input', u);
    u();
  }

  // Manejar checkboxes de eliminación para auto-rellenar descripción
  var checks = document.querySelectorAll('[name^="eliminar_"]');
  if (checks.length > 0 && ta) {
    checks.forEach(function(cb) {
      cb.addEventListener('change', function() {
        var items = [];
        checks.forEach(function(c) {
          if (c.checked) items.push(c.parentElement.querySelector('span').textContent.trim());
        });
        if (items.length > 0 && ta.value.trim() === '') {
          ta.value = 'Solicito eliminar: ' + items.join('; ') + '.';
          if (ct) ct.textContent = ta.value.length;
        }
      });
    });
  }
})();
</script>

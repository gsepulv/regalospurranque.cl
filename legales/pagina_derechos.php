<?php
/**
 * Controlador: Formulario de Ejercicio de Derechos ARCO
 * Ruta: /derechos
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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF (ajustar si ya tienes sistema de tokens)
    // if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    //     $errores[] = 'Token de seguridad inválido. Recarga la página.';
    // }

    $tipo        = trim($_POST['tipo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $rut         = trim($_POST['rut'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $tipos_validos = ['acceso', 'rectificacion', 'cancelacion', 'oposicion', 'portabilidad'];

    if (!in_array($tipo, $tipos_validos)) {
        $errores[] = 'Selecciona un tipo de solicitud válido.';
    }
    if (empty($nombre) || strlen($nombre) < 3) {
        $errores[] = 'El nombre es obligatorio (mínimo 3 caracteres).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un email válido.';
    }
    if (empty($descripcion) || strlen($descripcion) < 20) {
        $errores[] = 'La descripción debe tener al menos 20 caracteres.';
    }
    if (strlen($descripcion) > 5000) {
        $errores[] = 'La descripción no puede superar los 5.000 caracteres.';
    }

    // Rate limiting simple: máx 3 solicitudes por email en 24h
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
                ':descripcion' => $descripcion,
                ':ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]);

            $id_solicitud = $pdo->lastInsertId();

            // Notificar al admin por email
            $asunto_admin = "Nueva solicitud ARCO #{$id_solicitud} - {$tipo}";
            $cuerpo_admin = "Se ha recibido una solicitud de derechos ARCO:\n\n"
                . "ID: #{$id_solicitud}\n"
                . "Tipo: {$tipo}\n"
                . "Nombre: {$nombre}\n"
                . "Email: {$email}\n"
                . "RUT: " . ($rut ?: 'No informado') . "\n"
                . "Descripción: {$descripcion}\n\n"
                . "Plazo de respuesta: 10 días hábiles.\n"
                . "Gestionar en el panel de administración.";
            @mail('contacto@purranque.info', $asunto_admin, $cuerpo_admin, 
                "From: noreply@regalospurranque.cl\r\nContent-Type: text/plain; charset=UTF-8");

            // Confirmación al solicitante
            $asunto_user = "Solicitud ARCO recibida - Regalos Purranque";
            $cuerpo_user = "Estimado/a {$nombre},\n\n"
                . "Hemos recibido su solicitud de ejercicio de derechos ({$tipo}).\n\n"
                . "Número de seguimiento: #{$id_solicitud}\n"
                . "Fecha: " . date('d/m/Y H:i') . "\n"
                . "Plazo de respuesta: 10 días hábiles.\n\n"
                . "Le responderemos al email proporcionado dentro del plazo legal.\n\n"
                . "Atentamente,\n"
                . "Regalos Purranque\n"
                . "contacto@purranque.info";
            @mail($email, $asunto_user, $cuerpo_user, 
                "From: contacto@purranque.info\r\nContent-Type: text/plain; charset=UTF-8");

            $mensaje_exito = "Solicitud #{$id_solicitud} recibida correctamente. Recibirás confirmación en tu email. Plazo de respuesta: 10 días hábiles.";

        } catch (PDOException $e) {
            $mensaje_error = 'Error al procesar la solicitud. Intenta nuevamente o escríbenos a contacto@purranque.info';
        }
    }
}

// Generar CSRF token
// $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Labels para los tipos
$tipos_label = [
    'acceso'         => '🔍 Acceso — Quiero saber qué datos tienen sobre mí',
    'rectificacion'  => '✏️ Rectificación — Mis datos están incorrectos y quiero corregirlos',
    'cancelacion'    => '🗑️ Cancelación — Quiero que eliminen mis datos personales',
    'oposicion'      => '🚫 Oposición — No quiero que traten mis datos para cierta finalidad',
    'portabilidad'   => '📦 Portabilidad — Quiero recibir mis datos en formato estructurado',
];
?>
<!-- 
  PLANTILLA HTML para /derechos
  Insertar dentro de tu layout principal (header + footer)
  Ajustar clases CSS según tu framework (Tailwind, Bootstrap, custom)
-->

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
      (Acceso, Rectificación, Cancelación, Oposición) y de Portabilidad a través de este formulario.
    </p>
    <p>
      <strong>Plazo de respuesta:</strong> 10 días hábiles desde la recepción de tu solicitud.<br>
      <strong>Contacto alternativo:</strong> <a href="mailto:contacto@purranque.info">contacto@purranque.info</a>
    </p>
  </div>

  <?php if (!empty($mensaje_exito)): ?>
    <div class="alert alert-success">
      <span class="alert-icon">✅</span>
      <p><?= htmlspecialchars($mensaje_exito) ?></p>
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

  <?php if (empty($mensaje_exito)): ?>
  <form method="POST" action="/derechos" class="form-derechos" novalidate>
    <!-- <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"> -->

    <div class="form-group">
      <label for="tipo">Tipo de solicitud <span class="required">*</span></label>
      <select name="tipo" id="tipo" required>
        <option value="">— Selecciona el derecho que deseas ejercer —</option>
        <?php foreach ($tipos_label as $val => $label): ?>
          <option value="<?= $val ?>" <?= (isset($tipo) && $tipo === $val) ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

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
      </div>
    </div>

    <div class="form-group">
      <label for="rut">RUT (opcional)</label>
      <input type="text" name="rut" id="rut" maxlength="12"
             value="<?= htmlspecialchars($rut ?? '') ?>"
             placeholder="12.345.678-9">
      <small class="form-help">Ayuda a identificarte más rápidamente. No es obligatorio.</small>
    </div>

    <div class="form-group">
      <label for="descripcion">Descripción de tu solicitud <span class="required">*</span></label>
      <textarea name="descripcion" id="descripcion" required 
                minlength="20" maxlength="5000" rows="5"
                placeholder="Describe con detalle qué datos quieres consultar, corregir, eliminar o sobre los cuales deseas ejercer algún derecho. Mientras más específico, más rápida será la respuesta."><?= htmlspecialchars($descripcion ?? '') ?></textarea>
      <small class="form-help char-count">
        <span id="char-count">0</span> / 5.000 caracteres
      </small>
    </div>

    <div class="form-legal-notice">
      <p>
        📋 Al enviar este formulario aceptas que tus datos (nombre, email, descripción) 
        sean tratados exclusivamente para gestionar tu solicitud, conforme a nuestra 
        <a href="/privacidad">Política de Privacidad</a>. 
        Tus datos serán conservados hasta la resolución de la solicitud.
      </p>
    </div>

    <button type="submit" class="btn btn-primary">
      📨 Enviar solicitud
    </button>
  </form>
  <?php endif; ?>

  <!-- Información complementaria -->
  <div class="derechos-info">
    <h2>¿Qué son los derechos ARCO?</h2>
    
    <div class="derechos-grid">
      <div class="derecho-card">
        <span class="derecho-icon">🔍</span>
        <h3>Acceso</h3>
        <p>Conocer qué datos personales tenemos almacenados sobre ti y cómo los utilizamos.</p>
      </div>
      <div class="derecho-card">
        <span class="derecho-icon">✏️</span>
        <h3>Rectificación</h3>
        <p>Solicitar la corrección de datos personales inexactos o incompletos.</p>
      </div>
      <div class="derecho-card">
        <span class="derecho-icon">🗑️</span>
        <h3>Cancelación</h3>
        <p>Solicitar la eliminación de tus datos personales cuando ya no sean necesarios.</p>
      </div>
      <div class="derecho-card">
        <span class="derecho-icon">🚫</span>
        <h3>Oposición</h3>
        <p>Oponerte al tratamiento de tus datos para finalidades específicas.</p>
      </div>
      <div class="derecho-card">
        <span class="derecho-icon">📦</span>
        <h3>Portabilidad</h3>
        <p>Recibir tus datos en un formato estructurado y de uso común.</p>
      </div>
    </div>

    <div class="derechos-legal-ref">
      <p>
        <strong>Base legal:</strong> Ley 19.628 sobre Protección de la Vida Privada, 
        modificada por Ley 21.719 sobre Protección de Datos Personales.<br>
        <strong>Responsable:</strong> Regalos Purranque — PurranQUE.INFO<br>
        <strong>Contacto:</strong> <a href="mailto:contacto@purranque.info">contacto@purranque.info</a><br>
        <strong>Jurisdicción:</strong> Tribunales de Osorno, Región de Los Lagos, Chile.
      </p>
    </div>
  </div>

</div>

<script>
// Contador de caracteres
(function() {
  var textarea = document.getElementById('descripcion');
  var counter = document.getElementById('char-count');
  if (textarea && counter) {
    function update() { counter.textContent = textarea.value.length; }
    textarea.addEventListener('input', update);
    update();
  }
})();
</script>

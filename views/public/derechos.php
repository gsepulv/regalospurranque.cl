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
      <strong>Contacto alternativo:</strong> <a href="mailto:regalospurranque@gmail.com">regalospurranque@gmail.com</a>
    </p>
  </div>

  <?php if (!empty($mensajeExito)): ?>
    <div class="alert alert-success">
      <span class="alert-icon">&#x2705;</span>
      <div>
        <p><strong>Solicitud enviada correctamente</strong></p>
        <p><?= e($mensajeExito) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($mensajeError)): ?>
    <div class="alert alert-error">
      <span class="alert-icon">&#x274C;</span>
      <p><?= e($mensajeError) ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-error">
      <span class="alert-icon">&#x26A0;&#xFE0F;</span>
      <ul>
        <?php foreach ($errores as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- ====================================================== -->
  <!-- PASO 1: Elegir qué derecho ejercer (cards clickeables) -->
  <!-- ====================================================== -->
  <?php if (empty($mensajeExito) && empty($tipoSeleccionado)): ?>

  <h2>¿Qué necesitas hacer?</h2>
  <p>Selecciona la opción que mejor describe tu solicitud:</p>

  <div class="derechos-grid">
    <?php foreach ($tiposConfig as $key => $config): ?>
      <a href="<?= url('/derechos?tipo=' . $key) ?>" class="derecho-card derecho-card--clickable">
        <span class="derecho-icon"><?= $config['icono'] ?></span>
        <h3><?= e($config['titulo']) ?></h3>
        <p><?= e($config['desc']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Accesos rápidos para casos comunes -->
  <div class="casos-comunes">
    <h3>Casos frecuentes</h3>
    <div class="casos-grid">
      <a href="<?= url('/derechos?tipo=cancelacion') ?>" class="caso-card">
        <span>&#x1F3EA;</span>
        <strong>Soy comerciante y quiero eliminar mi negocio de la plataforma</strong>
      </a>
      <a href="<?= url('/derechos?tipo=cancelacion') ?>" class="caso-card">
        <span>&#x1F4AC;</span>
        <strong>Quiero eliminar una reseña que publiqué</strong>
      </a>
      <a href="<?= url('/derechos?tipo=acceso') ?>" class="caso-card">
        <span>&#x1F50E;</span>
        <strong>Quiero saber qué datos tienen sobre mí</strong>
      </a>
      <a href="<?= url('/derechos?tipo=rectificacion') ?>" class="caso-card">
        <span>&#x1F4DD;</span>
        <strong>Los datos de mi comercio están incorrectos</strong>
      </a>
    </div>
  </div>

  <?php endif; ?>

  <!-- ====================================================== -->
  <!-- PASO 2: Formulario específico según tipo seleccionado  -->
  <!-- ====================================================== -->
  <?php if (empty($mensajeExito) && !empty($tipoSeleccionado) && isset($tiposConfig[$tipoSeleccionado])): ?>
  <?php $cfg = $tiposConfig[$tipoSeleccionado]; ?>

  <div class="form-header">
    <a href="<?= url('/derechos') ?>" class="btn-volver">&larr; Volver a opciones</a>
    <div class="form-header-title">
      <span class="form-header-icon"><?= $cfg['icono'] ?></span>
      <div>
        <h2><?= e($cfg['titulo']) ?></h2>
        <p><?= e($cfg['desc']) ?></p>
      </div>
    </div>
  </div>

  <form method="POST" action="<?= url('/derechos') ?>" class="form-derechos" id="arcoForm" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="tipo" value="<?= e($tipoSeleccionado) ?>">

    <!-- ¿Eres comerciante registrado? -->
    <div class="form-group">
      <label class="checkbox-label">
        <input type="checkbox" name="es_comerciante" id="es_comerciante" value="1"
               <?= !empty($old['es_comerciante']) ? 'checked' : '' ?>
               onchange="document.getElementById('campos-comerciante').style.display = this.checked ? 'block' : 'none'">
        <span>Soy comerciante registrado en Regalos Purranque</span>
      </label>
    </div>

    <!-- Campos extra para comerciante -->
    <div id="campos-comerciante" style="display: <?= !empty($old['es_comerciante']) ? 'block' : 'none' ?>;">
      <div class="form-group">
        <label for="nombre_comercio">Nombre de tu comercio en la plataforma</label>
        <input type="text" name="nombre_comercio" id="nombre_comercio" maxlength="255"
               value="<?= e($old['nombre_comercio'] ?? '') ?>"
               placeholder="Ej: Flores Las Camelias, Panadería Don Pedro...">
      </div>
    </div>

    <!-- Campos comunes -->
    <div class="form-row">
      <div class="form-group">
        <label for="nombre">Nombre completo <span class="required">*</span></label>
        <input type="text" name="nombre" id="nombre" required minlength="3" maxlength="255"
               value="<?= e($old['nombre'] ?? '') ?>"
               placeholder="Tu nombre completo">
      </div>

      <div class="form-group">
        <label for="email">Correo electrónico <span class="required">*</span></label>
        <input type="email" name="email" id="email" required maxlength="255"
               value="<?= e($old['email'] ?? '') ?>"
               placeholder="tu@email.com">
        <small class="form-help">Recibirás la respuesta en este correo.</small>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="rut">RUT (opcional)</label>
        <input type="text" name="rut" id="rut" maxlength="12"
               value="<?= e($old['rut'] ?? '') ?>"
               placeholder="12.345.678-9">
        <small class="form-help">Ayuda a identificarte más rápidamente.</small>
      </div>

      <div class="form-group">
        <label for="telefono">Teléfono de contacto (opcional)</label>
        <input type="tel" name="telefono" id="telefono" maxlength="15"
               value="<?= e($old['telefono'] ?? '') ?>"
               placeholder="+56 9 1234 5678">
        <small class="form-help">En caso de necesitar contactarte por otra vía.</small>
      </div>
    </div>

    <!-- Motivo de baja (solo para cancelación) -->
    <?php if ($tipoSeleccionado === 'cancelacion'): ?>
    <div class="form-group">
      <label for="motivo_baja">Motivo de la solicitud</label>
      <select name="motivo_baja" id="motivo_baja">
        <option value="">— Selecciona un motivo (opcional) —</option>
        <?php foreach ($motivosBaja as $val => $label): ?>
          <option value="<?= e($val) ?>" <?= (($old['motivo_baja'] ?? '') === $val) ? 'selected' : '' ?>>
            <?= e($label) ?>
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
                placeholder="<?= e($cfg['placeholder']) ?>"><?= e($old['descripcion'] ?? '') ?></textarea>
      <small class="form-help char-count">
        <span id="char-count">0</span> / 5.000 caracteres
      </small>
    </div>

    <!-- Aviso legal -->
    <div class="form-legal-notice">
      <p>
        &#x1F4CB; Al enviar este formulario aceptas que tus datos (nombre, email, descripción)
        sean tratados exclusivamente para gestionar tu solicitud, conforme a nuestra
        <a href="<?= url('/privacidad') ?>">Política de Privacidad</a>.
        Tus datos serán conservados hasta la resolución de la solicitud.
      </p>
    </div>

    <?= \App\Services\Captcha::widget() ?>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" id="derechosSubmit">
        &#x1F4E8; Enviar solicitud
      </button>
      <a href="<?= url('/derechos') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
  <?php endif; ?>

  <!-- ====================================================== -->
  <!-- Información complementaria (siempre visible)           -->
  <!-- ====================================================== -->
  <div class="derechos-info">
    <h2>¿Qué son los derechos ARCO?</h2>
    <p>La ley chilena de protección de datos personales te garantiza estos derechos:</p>

    <div class="derechos-explicacion">
      <?php foreach ($tiposConfig as $key => $config): ?>
      <div class="derecho-explica">
        <span class="derecho-explica-icon"><?= $config['icono'] ?></span>
        <div>
          <h3><?= e($config['titulo']) ?></h3>
          <p><?= e($config['desc']) ?></p>
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
        <strong>Contacto:</strong> <a href="mailto:regalospurranque@gmail.com">regalospurranque@gmail.com</a><br>
        <strong>Jurisdicción:</strong> Tribunales de Osorno, Región de Los Lagos, Chile.
      </p>
    </div>
  </div>
</div>

<script>
(function() {
  var ta = document.getElementById('descripcion');
  var ct = document.getElementById('char-count');
  if (ta && ct) {
    function u() { ct.textContent = ta.value.length; }
    ta.addEventListener('input', u);
    u();
  }

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

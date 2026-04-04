<?php
/**
 * Registro de comercio — Paso 1: Crear cuenta
 */
$errores = $_SESSION['flash_errors'] ?? [];
$old     = $_SESSION['flash_old'] ?? [];
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_error']);
?>

<style>
/* Panel desplegable de términos */
.terminos-panel { border:2px solid #F97316; border-left:6px solid #F97316; border-radius:10px; margin-bottom:1.5rem; background:#FFF7ED; overflow:hidden; }
.terminos-panel summary { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; cursor:pointer; list-style:none; user-select:none; gap:0.5rem; }
.terminos-panel summary::-webkit-details-marker { display:none; }
.terminos-panel summary::marker { display:none; content:""; }
.terminos-panel .terminos-arrow { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:#FDBA74; color:#9A3412; font-size:0.85rem; transition:transform 0.25s ease; flex-shrink:0; }
.terminos-panel[open] .terminos-arrow { transform:rotate(180deg); }
.terminos-panel .terminos-titulo { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; }
.terminos-panel .terminos-titulo h3 { margin:0; font-size:1rem; color:#9A3412; text-transform:uppercase; letter-spacing:0.5px; }
.terminos-badge { display:inline-flex; align-items:center; padding:0.15rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:700; }
.terminos-badge--pendiente { background:#FEE2E2; color:#991B1B; }
.terminos-badge--ok { background:#D1FAE5; color:#065F46; }
.terminos-body { padding:0 1.25rem 1.25rem; }
.terminos-body > p { margin:0 0 1rem; font-size:0.8rem; color:#B45309; }
.terminos-item { background:#fff; border:1px solid #FED7AA; border-radius:8px; padding:0.75rem 1rem; margin-bottom:0.5rem; transition:box-shadow 0.2s; }
.terminos-item--pendiente { box-shadow:0 0 0 2px #FECACA; }
</style>

<section class="section">
    <div class="container" style="max-width:520px">

        <div style="text-align:center;margin-bottom:2rem">
            <span style="font-size:3rem">🏪</span>
            <h1 style="font-size:1.75rem;margin:0.5rem 0">Registra tu comercio</h1>
            <p class="text-muted">Publica tu negocio en el directorio digital de Purranque. <strong>Es gratis.</strong></p>
            <p style="font-size:var(--font-size-sm);color:var(--color-gray)">
                Paso <strong>1</strong> de <strong>2</strong> — Crear tu cuenta
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?php foreach ($errores as $err): ?>
                    <p style="margin:0.25rem 0"><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
            <form method="POST" action="<?= url('/registrar-comercio/cuenta') ?>" id="formCuenta">
                <?= csrf_field() ?>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Tu nombre completo <span style="color:#DC2626">*</span>
                    </label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= e($old['nombre'] ?? '') ?>"
                           placeholder="Ej: María González" minlength="3" maxlength="100" required>
                    <small style="color:var(--color-gray)">Min. 3, max. 100 caracteres.</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Email <span style="color:#DC2626">*</span>
                    </label>
                    <input type="email" name="email" class="form-control"
                           value="<?= e($old['email'] ?? '') ?>"
                           placeholder="tu@email.com" maxlength="100" required>
                    <small style="color:#6B7280;font-size:0.8rem">Será tu usuario para acceder</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Teléfono / WhatsApp
                    </label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= e($old['telefono'] ?? '') ?>"
                           placeholder="+56 9 XXXX XXXX" minlength="9" maxlength="15" required>
                    <small style="color:var(--color-gray)">Min. 9, max. 15 caracteres.</small>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password" id="pw1" class="form-control"
                           placeholder="Mínimo 8 caracteres" minlength="8" required>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">
                        Confirmar contraseña <span style="color:#DC2626">*</span>
                    </label>
                    <input type="password" name="password_confirm" id="pw2" class="form-control"
                           placeholder="Repite tu contraseña" minlength="8" required>
                </div>

                <?php
                $politicas = [
                    'terminos'    => ['nombre' => 'Términos y Condiciones',  'url' => url('/terminos')],
                    'privacidad'  => ['nombre' => 'Política de Privacidad',  'url' => url('/privacidad')],
                    'contenidos'  => ['nombre' => 'Política de Contenidos',  'url' => url('/contenidos')],
                    'derechos'    => ['nombre' => 'Ejercicio de Derechos',   'url' => url('/derechos')],
                    'cookies'     => ['nombre' => 'Política de Cookies',     'url' => url('/cookies')],
                ];
                ?>
                <details class="terminos-panel" id="terminosPanel">
                    <summary>
                        <div class="terminos-titulo">
                            <h3>📋 Términos y Políticas</h3>
                            <span class="terminos-badge terminos-badge--pendiente" id="terminosBadge">0 de 5 aceptadas</span>
                        </div>
                        <span class="terminos-arrow">▼</span>
                    </summary>
                    <div class="terminos-body">
                        <p>Lee cada política y selecciona tu decisión. <strong>Debes aceptar todas para registrarte.</strong></p>

                        <?php foreach ($politicas as $slug => $pol): ?>
                        <div class="terminos-item" id="item-<?= $slug ?>">
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem">
                                <a href="<?= $pol['url'] ?>" target="_blank" style="font-weight:600;font-size:0.9rem;color:#1D4ED8;text-decoration:underline">
                                    <?= $pol['nombre'] ?>
                                </a>
                                <div style="display:flex;gap:1rem">
                                    <label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem;color:#15803D;font-weight:600">
                                        <input type="radio" name="politica_<?= $slug ?>" value="acepto"
                                               <?= ($old['politica_' . $slug] ?? '') === 'acepto' ? 'checked' : '' ?>>
                                        Acepto
                                    </label>
                                    <label style="display:flex;align-items:center;gap:0.3rem;cursor:pointer;font-size:0.85rem;color:#DC2626;font-weight:600">
                                        <input type="radio" name="politica_<?= $slug ?>" value="rechazo"
                                               <?= ($old['politica_' . $slug] ?? '') === 'rechazo' ? 'checked' : '' ?>>
                                        Rechazo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div id="politicas-error" style="display:none;background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.5rem 0.75rem;border-radius:6px;margin-top:0.75rem;font-size:0.85rem"></div>
                    </div>
                </details>

                <?= \App\Services\Captcha::widget() ?>

                <button type="submit" class="btn btn--primary" style="width:100%;padding:0.75rem;font-size:1rem">
                    Continuar → Datos del comercio
                </button>
            </form>
        </div>

        <div style="text-align:center;margin-top:1.5rem">
            <p style="font-size:0.8rem;color:#6B7280">
                🔒 Tu información está segura y no será compartida.<br>
                Tu comercio será revisado antes de ser publicado.
            </p>
        </div>

    </div>
</section>

<script>
(function() {
    var politicas = ['terminos','privacidad','contenidos','derechos','cookies'];
    var panel = document.getElementById('terminosPanel');
    var badge = document.getElementById('terminosBadge');
    var errorDiv = document.getElementById('politicas-error');

    function contarAceptadas() {
        var aceptadas = 0;
        for (var i = 0; i < politicas.length; i++) {
            var radios = document.getElementsByName('politica_' + politicas[i]);
            for (var j = 0; j < radios.length; j++) {
                if (radios[j].checked && radios[j].value === 'acepto') aceptadas++;
            }
        }
        return aceptadas;
    }

    function actualizarBadge() {
        var n = contarAceptadas();
        badge.textContent = n + ' de 5 aceptadas';
        if (n === 5) {
            badge.className = 'terminos-badge terminos-badge--ok';
        } else {
            badge.className = 'terminos-badge terminos-badge--pendiente';
        }
    }

    for (var i = 0; i < politicas.length; i++) {
        var radios = document.getElementsByName('politica_' + politicas[i]);
        for (var j = 0; j < radios.length; j++) {
            radios[j].addEventListener('change', function() {
                actualizarBadge();
                var item = this.closest('.terminos-item');
                if (item) item.classList.remove('terminos-item--pendiente');
            });
        }
    }

    actualizarBadge();

    <?php if (!empty($old) && (!empty($errores) || !empty($error))): ?>
    panel.open = true;
    <?php endif; ?>

    document.getElementById('formCuenta').addEventListener('submit', function(e) {
        var mensajes = [];
        var pendientes = [];

        for (var i = 0; i < politicas.length; i++) {
            var radios = document.getElementsByName('politica_' + politicas[i]);
            var seleccionado = false;
            var esRechazo = false;
            for (var j = 0; j < radios.length; j++) {
                if (radios[j].checked) {
                    seleccionado = true;
                    if (radios[j].value === 'rechazo') esRechazo = true;
                }
            }
            if (!seleccionado || esRechazo) {
                pendientes.push(politicas[i]);
            }
        }

        if (pendientes.length > 0) {
            if (pendientes.length === 5) {
                mensajes.push('Debes seleccionar Acepto en todas las pol\u00edticas para registrarte.');
            } else {
                mensajes.push('Hay pol\u00edticas pendientes o rechazadas. Debes aceptar todas para registrarte.');
            }
        }

        if (document.getElementById('pw1').value !== document.getElementById('pw2').value) {
            mensajes.push('Las contrase\u00f1as no coinciden.');
        }

        if (mensajes.length > 0) {
            e.preventDefault();

            if (pendientes.length > 0) {
                panel.open = true;
                for (var k = 0; k < politicas.length; k++) {
                    var item = document.getElementById('item-' + politicas[k]);
                    if (item) {
                        if (pendientes.indexOf(politicas[k]) !== -1) {
                            item.classList.add('terminos-item--pendiente');
                        } else {
                            item.classList.remove('terminos-item--pendiente');
                        }
                    }
                }
            }

            errorDiv.style.display = 'block';
            errorDiv.innerHTML = mensajes.join('<br>');
            errorDiv.scrollIntoView({behavior: 'smooth', block: 'center'});
        } else {
            errorDiv.style.display = 'none';
        }
    });
})();
</script>

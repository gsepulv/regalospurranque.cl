<?php
/**
 * Partial: Alertas de vencimiento y formulario de renovación
 * Variables del dashboard: $comercio, $plan, $renovacion, $renovacionesActivas,
 *                         $planesDisponibles, $metodosPago, $datosBanco
 */
if (!$comercio || !$renovacionesActivas) return;

// Calcular días restantes
$diasRestantes = null;
if (!empty($comercio['plan_fin'])) {
    $hoy = new DateTime('today');
    $fin = new DateTime($comercio['plan_fin']);
    $diff = $hoy->diff($fin);
    $diasRestantes = $diff->invert ? -$diff->days : $diff->days;
}

// Si plan_fin es NULL (comercio antiguo sin vigencia), no mostrar alertas
if ($diasRestantes === null) return;
?>

<?php if ($renovacion): ?>
    <!-- Solicitud pendiente -->
    <div style="background:#DBEAFE;border:1px solid #BFDBFE;border-radius:12px;padding:1rem;margin-bottom:1.25rem">
        <p style="margin:0;font-size:0.9rem;color:#1E40AF">
            &#128338; <strong>Tienes una solicitud de renovaci&oacute;n pendiente</strong> enviada el <?= date('d/m/Y H:i', strtotime($renovacion['created_at'])) ?>.
            Nuestro equipo la revisar&aacute; pronto.
        </p>
        <?php if ($renovacion['plan_solicitado']): ?>
            <p style="margin:0.5rem 0 0;font-size:0.85rem;color:#1E40AF">
                Plan solicitado: <strong><?= e($renovacion['plan_solicitado']) ?></strong>
                <?php if ($renovacion['monto']): ?>
                    &mdash; $<?= number_format((float)$renovacion['monto'], 0, ',', '.') ?> CLP
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

<?php elseif ($diasRestantes <= 7 && $diasRestantes >= 0): ?>
    <!-- Vence pronto -->
    <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:1rem;margin-bottom:1.25rem">
        <p style="margin:0;font-size:0.9rem;color:#92400E">
            &#9888;&#65039; <strong>Tu plan vence <?= $diasRestantes === 0 ? 'hoy' : 'en ' . $diasRestantes . ' d&iacute;a' . ($diasRestantes > 1 ? 's' : '') ?></strong>
            (<?= date('d/m/Y', strtotime($comercio['plan_fin'])) ?>).
            Renueva para mantener tu comercio visible en el directorio.
        </p>
        <button type="button" onclick="document.getElementById('formRenovacion').style.display='block';this.style.display='none'"
                style="margin-top:0.75rem;background:#D97706;color:white;border:none;padding:0.5rem 1.25rem;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer">
            Solicitar renovaci&oacute;n
        </button>
    </div>

<?php elseif ($diasRestantes < 0): ?>
    <!-- Plan vencido -->
    <div style="background:#FEE2E2;border:1px solid #FECACA;border-radius:12px;padding:1rem;margin-bottom:1.25rem">
        <p style="margin:0;font-size:0.9rem;color:#991B1B">
            &#10060; <strong>Tu plan venci&oacute; el <?= date('d/m/Y', strtotime($comercio['plan_fin'])) ?></strong>.
            Tu comercio no es visible en el directorio. Solicita la reactivaci&oacute;n para volver a aparecer.
        </p>
        <button type="button" onclick="document.getElementById('formRenovacion').style.display='block';this.style.display='none'"
                style="margin-top:0.75rem;background:#DC2626;color:white;border:none;padding:0.5rem 1.25rem;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer">
            Solicitar reactivaci&oacute;n
        </button>
    </div>

<?php else: ?>
    <?php return; /* Más de 7 días: no mostrar nada */ ?>
<?php endif; ?>

<?php if (!$renovacion && $diasRestantes <= 7): ?>
<!-- Formulario de renovación (oculto por defecto) -->
<div id="formRenovacion" style="display:none;background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
    <h3 style="margin:0 0 1rem;font-size:1.1rem">Solicitar renovaci&oacute;n</h3>

    <form method="POST" action="<?= url('/mi-comercio/solicitar-renovacion') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Plan -->
        <div style="margin-bottom:1rem">
            <label style="display:block;font-weight:600;font-size:0.9rem;margin-bottom:0.35rem">Plan deseado</label>
            <select name="plan_solicitado" required
                    style="width:100%;padding:0.5rem;border:1px solid #D1D5DB;border-radius:8px;font-size:0.9rem">
                <?php foreach ($planesDisponibles as $p): ?>
                    <option value="<?= e($p['slug']) ?>"
                            <?= $p['slug'] === $comercio['plan'] ? 'selected' : '' ?>
                            data-precio="<?= (float)($p['precio_regular'] ?? 0) ?>"
                            data-duracion="<?= (int)($p['duracion_dias'] ?? 30) ?>">
                        <?= e($p['icono'] ?? '') ?> <?= e($p['nombre']) ?>
                        <?php if ((float)($p['precio_regular'] ?? 0) > 0): ?>
                            &mdash; $<?= number_format((float)$p['precio_regular'], 0, ',', '.') ?> CLP
                        <?php else: ?>
                            &mdash; Gratis
                        <?php endif; ?>
                        (<?= (int)($p['duracion_dias'] ?? 30) ?> d&iacute;as)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Método de pago -->
        <div style="margin-bottom:1rem">
            <label style="display:block;font-weight:600;font-size:0.9rem;margin-bottom:0.35rem">M&eacute;todo de pago</label>
            <select name="metodo_pago" id="metodoPago" required
                    onchange="toggleDatosBanco(this.value)"
                    style="width:100%;padding:0.5rem;border:1px solid #D1D5DB;border-radius:8px;font-size:0.9rem">
                <?php foreach ($metodosPago as $metodo): ?>
                    <option value="<?= e($metodo) ?>"><?= e(ucfirst($metodo)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Datos bancarios (solo para transferencia) -->
        <?php if (!empty($datosBanco['nombre']) || !empty($datosBanco['cuenta'])): ?>
            <div id="datosBanco" style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:1rem;margin-bottom:1rem;font-size:0.85rem">
                <p style="margin:0 0 0.5rem;font-weight:600;color:#166534">Datos para transferencia:</p>
                <?php if (!empty($datosBanco['nombre'])): ?>
                    <p style="margin:0 0 0.15rem;color:#166534"><strong>Nombre:</strong> <?= e($datosBanco['nombre']) ?></p>
                <?php endif; ?>
                <?php if (!empty($datosBanco['rut'])): ?>
                    <p style="margin:0 0 0.15rem;color:#166534"><strong>RUT:</strong> <?= e($datosBanco['rut']) ?></p>
                <?php endif; ?>
                <?php if (!empty($datosBanco['tipo'])): ?>
                    <p style="margin:0 0 0.15rem;color:#166534"><strong>Tipo:</strong> <?= e($datosBanco['tipo']) ?></p>
                <?php endif; ?>
                <?php if (!empty($datosBanco['cuenta'])): ?>
                    <p style="margin:0 0 0.15rem;color:#166534"><strong>Cuenta:</strong> <?= e($datosBanco['cuenta']) ?></p>
                <?php endif; ?>
                <?php if (!empty($datosBanco['email'])): ?>
                    <p style="margin:0;color:#166534"><strong>Email:</strong> <?= e($datosBanco['email']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Fecha de pago -->
        <div style="margin-bottom:1rem">
            <label style="display:block;font-weight:600;font-size:0.9rem;margin-bottom:0.35rem">Fecha de pago (opcional)</label>
            <input type="date" name="fecha_pago" value="<?= date('Y-m-d') ?>"
                   style="width:100%;padding:0.5rem;border:1px solid #D1D5DB;border-radius:8px;font-size:0.9rem">
        </div>

        <!-- Comprobante -->
        <div id="comprobanteWrapper" style="margin-bottom:1rem">
            <label style="display:block;font-weight:600;font-size:0.9rem;margin-bottom:0.35rem">Comprobante de pago (imagen)</label>
            <input type="file" name="comprobante" accept="image/*"
                   style="width:100%;padding:0.5rem;border:1px solid #D1D5DB;border-radius:8px;font-size:0.85rem">
            <small style="color:#6B7280;font-size:0.8rem">JPG, PNG o WebP. M&aacute;ximo 2 MB.</small>
        </div>

        <button type="submit"
                style="background:#2563EB;color:white;border:none;padding:0.6rem 1.5rem;border-radius:8px;font-size:0.9rem;font-weight:600;cursor:pointer"
                onclick="return confirm('&iquest;Enviar solicitud de renovaci&oacute;n?')">
            Enviar solicitud
        </button>
        <button type="button" onclick="document.getElementById('formRenovacion').style.display='none'"
                style="background:transparent;color:#6B7280;border:1px solid #D1D5DB;padding:0.6rem 1.5rem;border-radius:8px;font-size:0.9rem;cursor:pointer;margin-left:0.5rem">
            Cancelar
        </button>
    </form>
</div>

<script>
function toggleDatosBanco(metodo) {
    var db = document.getElementById('datosBanco');
    if (db) db.style.display = (metodo === 'transferencia') ? 'block' : 'none';
}
// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('metodoPago');
    if (sel) toggleDatosBanco(sel.value);
});
</script>
<?php endif; ?>

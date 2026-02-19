<?php
/**
 * Registro de comercio — Confirmación
 */
?>

<section class="section">
    <div class="container" style="max-width:520px;text-align:center">

        <div style="margin:2rem 0">
            <span style="font-size:4rem">🎉</span>
            <h1 style="font-size:1.75rem;margin:1rem 0 0.5rem">¡Registro exitoso!</h1>
            <p class="text-muted" style="font-size:1.05rem">
                Tu comercio ha sido recibido y está <strong>pendiente de revisión</strong>.
            </p>
        </div>

        <div style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);text-align:left;margin-bottom:1.5rem">
            <h3 style="margin:0 0 1rem;font-size:1rem">¿Qué sigue?</h3>

            <div style="display:flex;gap:0.75rem;margin-bottom:1rem">
                <span style="background:#DBEAFE;color:#1D4ED8;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0">1</span>
                <div>
                    <strong>Revisión</strong>
                    <p style="margin:0.15rem 0 0;font-size:0.9rem;color:#6B7280">Nuestro equipo revisará la información de tu comercio.</p>
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-bottom:1rem">
                <span style="background:#DBEAFE;color:#1D4ED8;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0">2</span>
                <div>
                    <strong>Aprobación</strong>
                    <p style="margin:0.15rem 0 0;font-size:0.9rem;color:#6B7280">Si todo está correcto, tu comercio será activado y publicado en el directorio.</p>
                </div>
            </div>

            <div style="display:flex;gap:0.75rem">
                <span style="background:#DBEAFE;color:#1D4ED8;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0">3</span>
                <div>
                    <strong>¡Listo!</strong>
                    <p style="margin:0.15rem 0 0;font-size:0.9rem;color:#6B7280">Tu negocio aparecerá en el directorio digital de Purranque y podrás recibir clientes.</p>
                </div>
            </div>
        </div>

        <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:1rem;margin-bottom:1.5rem">
            <p style="margin:0;font-size:0.9rem;color:#166534">
                💡 <strong>¿Quieres más visibilidad?</strong> Con nuestros planes pagados obtienes más fotos, todas las redes sociales, horarios, sello verificado y posición destacada.
                <a href="<?= url('/planes') ?>" style="color:#166534;font-weight:600">Ver planes →</a>
            </p>
        </div>

        <a href="<?= url('/') ?>" class="btn btn--primary" style="padding:0.65rem 2rem">
            Volver al inicio
        </a>

        <p style="font-size:0.8rem;color:#9CA3AF;margin-top:1rem">
            Si tienes dudas, escríbenos por WhatsApp o a contacto@purranque.info
        </p>

    </div>
</section>

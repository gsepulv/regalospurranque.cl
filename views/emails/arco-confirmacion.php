<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Solicitud recibida #<?= $solicitud['id'] ?></h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Estimado/a <?= htmlspecialchars($solicitud['nombre']) ?>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hemos recibido su solicitud de ejercicio de derechos sobre datos personales.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Tipo de solicitud:</strong> <?= htmlspecialchars($solicitud['tipo_texto']) ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Número de seguimiento:</strong> #<?= $solicitud['id'] ?>
            </p>
            <p style="margin:0;color:#64748b;font-size:13px;">
                <strong>Fecha:</strong> <?= htmlspecialchars($solicitud['fecha']) ?>
            </p>
        </td>
    </tr>
</table>

<p style="color:#b45309;font-size:14px;font-weight:600;margin:0 0 16px;">
    &#x23F0; Plazo de respuesta: 10 días hábiles.
</p>

<p style="color:#475569;margin:0 0 8px;line-height:1.6;">
    Le responderemos al email proporcionado dentro del plazo legal.
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Si necesita comunicarse antes, puede escribirnos a
    <a href="mailto:contacto@purranque.info" style="color:#2563eb">contacto@purranque.info</a>
</p>

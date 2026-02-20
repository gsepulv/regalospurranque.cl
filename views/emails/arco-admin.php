<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Solicitud ARCO #<?= $solicitud['id'] ?></h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha recibido una nueva solicitud de ejercicio de derechos.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Tipo:</strong> <?= htmlspecialchars($solicitud['tipo_texto']) ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Nombre:</strong> <?= htmlspecialchars($solicitud['nombre']) ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($solicitud['email']) ?>" style="color:#2563eb"><?= htmlspecialchars($solicitud['email']) ?></a>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>RUT:</strong> <?= htmlspecialchars($solicitud['rut'] ?: 'No informado') ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Teléfono:</strong> <?= htmlspecialchars($solicitud['telefono'] ?: 'No informado') ?>
            </p>
            <?php if ($solicitud['es_comerciante']): ?>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Comerciante:</strong> Sí — <?= htmlspecialchars($solicitud['nombre_comercio']) ?>
            </p>
            <?php endif; ?>
            <?php if (!empty($solicitud['motivo_baja'])): ?>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Motivo de baja:</strong> <?= htmlspecialchars($solicitud['motivo_baja']) ?>
            </p>
            <?php endif; ?>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:12px 0">
            <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($solicitud['descripcion']) ?></p>
        </td>
    </tr>
</table>

<p style="color:#b45309;font-size:14px;font-weight:600;margin:0 0 16px;">
    &#x23F0; Plazo de respuesta: 10 días hábiles (vence: <?= htmlspecialchars($solicitud['fecha_limite']) ?>)
</p>

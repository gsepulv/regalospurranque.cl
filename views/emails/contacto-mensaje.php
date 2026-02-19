<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Nuevo mensaje de contacto</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha recibido un nuevo mensaje desde el formulario de contacto.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Nombre:</strong> <?= htmlspecialchars($datos['nombre']) ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($datos['email']) ?>" style="color:#2563eb"><?= htmlspecialchars($datos['email']) ?></a>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Asunto:</strong> <?= htmlspecialchars($datos['asunto']) ?>
            </p>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:12px 0">
            <p style="margin:0;color:#334155;font-size:14px;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($datos['mensaje']) ?></p>
        </td>
    </tr>
</table>

<p style="color:#64748b;font-size:13px;margin:0 0 16px;">
    Puedes responder directamente a <a href="mailto:<?= htmlspecialchars($datos['email']) ?>" style="color:#2563eb"><?= htmlspecialchars($datos['email']) ?></a>
</p>

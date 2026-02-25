<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Sobre tu solicitud de renovaci&oacute;n</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <?= htmlspecialchars($usuario['nombre']) ?>, lamentamos informarte que tu solicitud de
    renovaci&oacute;n para <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> no ha sido aprobada.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef3c7;border:1px solid #fde68a;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 6px;color:#92400e;font-size:14px;font-weight:bold;">
                Motivo:
            </p>
            <p style="margin:0;color:#92400e;font-size:14px;line-height:1.6;">
                <?= htmlspecialchars($renovacion['motivo_rechazo'] ?? 'Sin motivo especificado') ?>
            </p>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 20px;line-height:1.6;">
    Si tienes dudas o deseas enviar una nueva solicitud, puedes contactarnos o acceder a tu panel.
</p>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?= $siteUrl ?>/mi-comercio/login" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Acceder a mi panel
    </a>
</p>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/contacto" style="color:#2563eb;font-size:14px;text-decoration:underline;">
        Contactar soporte
    </a>
</p>

<p style="color:#94a3b8;font-size:13px;margin:0;line-height:1.5;">
    Si no encuentras este correo en tu bandeja de entrada, revisa tu carpeta de Spam o Correo no deseado.
</p>

<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Email de prueba</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Este es un email de prueba enviado desde <strong><?= htmlspecialchars($siteName) ?></strong>.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;text-align:center;">
            <p style="margin:0 0 8px;color:#15803d;font-size:28px;">&#10004;</p>
            <p style="margin:0 0 8px;color:#15803d;font-size:16px;font-weight:bold;">
                El sistema de notificaciones funciona correctamente
            </p>
            <p style="margin:0;color:#166534;font-size:13px;">
                Enviado a: <?= htmlspecialchars($email) ?><br>
                Fecha: <?= date('d/m/Y H:i:s') ?>
            </p>
        </td>
    </tr>
</table>

<p style="color:#94a3b8;font-size:12px;text-align:center;margin:0;">
    Si recibes este email, las notificaciones están configuradas correctamente.
</p>

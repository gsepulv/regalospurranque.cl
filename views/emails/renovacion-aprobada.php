<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Tu renovaci&oacute;n ha sido aprobada</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <?= htmlspecialchars($usuario['nombre']) ?>, tu solicitud de renovaci&oacute;n para
    <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> ha sido aprobada.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 6px;color:#15803d;font-size:15px;font-weight:bold;">
                &#10003; Tu comercio est&aacute; activo nuevamente
            </p>
            <p style="margin:0 0 4px;color:#166534;font-size:14px;">
                <strong>Plan:</strong> <?= htmlspecialchars($plan['nombre'] ?? '') ?>
            </p>
            <p style="margin:0 0 4px;color:#166534;font-size:14px;">
                <strong>Vigencia desde:</strong> <?= htmlspecialchars($comercio['plan_inicio'] ?? date('Y-m-d')) ?>
            </p>
            <p style="margin:0;color:#166534;font-size:14px;">
                <strong>Vigencia hasta:</strong> <?= htmlspecialchars($comercio['plan_fin'] ?? '') ?>
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver mi comercio
    </a>
</p>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/mi-comercio/login" style="color:#2563eb;font-size:14px;text-decoration:underline;">
        Acceder a mi panel
    </a>
</p>

<p style="color:#94a3b8;font-size:13px;margin:0;line-height:1.5;">
    Si no encuentras este correo en tu bandeja de entrada, revisa tu carpeta de Spam o Correo no deseado.
</p>

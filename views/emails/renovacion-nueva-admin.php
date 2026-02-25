<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Nueva solicitud de renovaci&oacute;n</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    El comercio <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> ha solicitado una renovaci&oacute;n de plan.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#dbeafe;border:1px solid #bfdbfe;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 6px;color:#1e40af;font-size:14px;">
                <strong>Plan actual:</strong> <?= htmlspecialchars($comercio['plan'] ?? 'freemium') ?>
            </p>
            <p style="margin:0 0 6px;color:#1e40af;font-size:14px;">
                <strong>Plan solicitado:</strong> <?= htmlspecialchars($plan['nombre'] ?? $plan['slug'] ?? '') ?>
            </p>
            <p style="margin:0;color:#1e40af;font-size:14px;">
                <strong>Precio:</strong> $<?= number_format((int)($plan['precio_regular'] ?? 0), 0, ',', '.') ?> CLP
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/admin/renovaciones" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Revisar solicitud
    </a>
</p>

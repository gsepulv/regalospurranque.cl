<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Nuevo comercio registrado</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha registrado un nuevo comercio en <strong><?= htmlspecialchars($siteName) ?></strong>.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;font-size:15px;font-weight:bold;color:#1e40af;">Detalles:</p>
            <p style="margin:0 0 6px;color:#1e3a5f;font-size:14px;"><strong>Comercio:</strong> <?= htmlspecialchars($nombreComercio) ?></p>
            <p style="margin:0 0 6px;color:#1e3a5f;font-size:14px;"><strong>ID:</strong> <?= (int) $comercioId ?></p>
            <p style="margin:0;color:#1e3a5f;font-size:14px;"><strong>Estado:</strong> Pendiente de aprobaci&oacute;n</p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/comercios/editar/<?= (int) $comercioId ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Revisar comercio
    </a>
</p>

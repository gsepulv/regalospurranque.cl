<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Cambios pendientes de revisi&oacute;n</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    El comercio <strong><?= htmlspecialchars($nombreComercio) ?></strong> ha enviado cambios para revisi&oacute;n en <strong><?= htmlspecialchars($siteName) ?></strong>.
</p>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/comercios/editar/<?= (int) $comercioId ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Revisar cambios
    </a>
</p>

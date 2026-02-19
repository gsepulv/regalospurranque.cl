<h2 style="margin:0 0 16px;color:#dc2626;font-size:20px;">Error en el sistema</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha detectado un error en <strong><?= htmlspecialchars($siteName) ?></strong>:
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#991b1b;font-size:15px;font-weight:bold;">
                <?= htmlspecialchars($mensaje) ?>
            </p>
            <?php if (!empty($detalle)): ?>
                <pre style="margin:8px 0 0;color:#7f1d1d;font-size:12px;line-height:1.5;white-space:pre-wrap;word-break:break-all;background:#fee2e2;padding:12px;border-radius:4px;"><?= htmlspecialchars($detalle) ?></pre>
            <?php endif; ?>
            <p style="margin:12px 0 0;color:#64748b;font-size:12px;">
                <strong>Fecha:</strong> <?= htmlspecialchars($fecha) ?>
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/mantenimiento/logs" style="display:inline-block;background:#dc2626;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver logs del sistema
    </a>
</p>

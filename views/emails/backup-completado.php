<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Backup completado</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha generado un backup exitosamente en el sistema.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#15803d;font-size:14px;">
                <strong>Tipo:</strong> <?= htmlspecialchars(ucfirst($tipo)) ?>
            </p>
            <p style="margin:0 0 8px;color:#15803d;font-size:14px;">
                <strong>Archivo:</strong> <?= htmlspecialchars($archivo) ?>
            </p>
            <p style="margin:0 0 8px;color:#15803d;font-size:14px;">
                <strong>Tamaño:</strong> <?= htmlspecialchars($tamano) ?>
            </p>
            <p style="margin:0;color:#15803d;font-size:14px;">
                <strong>Fecha:</strong> <?= date('d/m/Y H:i:s') ?>
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/mantenimiento/backups" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver backups
    </a>
</p>

<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Resumen semanal</h2>

<p style="color:#475569;margin:0 0 20px;line-height:1.6;">
    Aquí tienes el resumen de actividad de la última semana en <strong><?= htmlspecialchars($siteName) ?></strong>:
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <!-- Visitas -->
    <tr>
        <td style="padding:12px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px 6px 0 0;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#1e40af;font-size:14px;font-weight:bold;">Visitas totales</td>
                    <td style="color:#1e40af;font-size:20px;font-weight:bold;text-align:right;"><?= number_format($stats['visitas'] ?? 0) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Comercios -->
    <tr>
        <td style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#475569;font-size:14px;">Comercios activos</td>
                    <td style="color:#1e293b;font-size:18px;font-weight:bold;text-align:right;"><?= number_format($stats['comercios_activos'] ?? 0) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Reseñas nuevas -->
    <tr>
        <td style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#475569;font-size:14px;">Reseñas nuevas</td>
                    <td style="color:#1e293b;font-size:18px;font-weight:bold;text-align:right;"><?= number_format($stats['resenas_nuevas'] ?? 0) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Reseñas pendientes -->
    <tr>
        <td style="padding:12px 16px;background:#fef3c7;border:1px solid #fde68a;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#92400e;font-size:14px;">Reseñas pendientes</td>
                    <td style="color:#92400e;font-size:18px;font-weight:bold;text-align:right;"><?= number_format($stats['resenas_pendientes'] ?? 0) ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <!-- Noticias -->
    <tr>
        <td style="padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0 0 6px 6px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="color:#475569;font-size:14px;">Noticias publicadas</td>
                    <td style="color:#1e293b;font-size:18px;font-weight:bold;text-align:right;"><?= number_format($stats['noticias'] ?? 0) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php if (!empty($stats['top_comercios'])): ?>
<p style="margin:0 0 8px;color:#1e293b;font-size:15px;font-weight:bold;">Comercios más visitados:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <?php foreach ($stats['top_comercios'] as $i => $c): ?>
    <tr>
        <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:13px;">
            <?= $i + 1 ?>. <?= htmlspecialchars($c['nombre']) ?>
        </td>
        <td style="padding:8px 12px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-size:13px;text-align:right;font-weight:bold;">
            <?= number_format($c['visitas']) ?> visitas
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/dashboard" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ir al dashboard
    </a>
</p>

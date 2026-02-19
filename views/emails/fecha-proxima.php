<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Fecha especial próxima</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se acerca una fecha especial en el calendario:
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:20px;text-align:center;">
            <?php if (!empty($fecha['icono'])): ?>
                <p style="margin:0 0 8px;font-size:32px;"><?= $fecha['icono'] ?></p>
            <?php endif; ?>
            <p style="margin:0 0 8px;font-size:20px;font-weight:bold;color:#581c87;">
                <?= htmlspecialchars($fecha['nombre']) ?>
            </p>
            <?php if (!empty($fecha['fecha_inicio'])): ?>
                <p style="margin:0 0 8px;color:#7c3aed;font-size:14px;">
                    <?= date('d/m/Y', strtotime($fecha['fecha_inicio'])) ?>
                    <?php if (!empty($fecha['fecha_fin']) && $fecha['fecha_fin'] !== $fecha['fecha_inicio']): ?>
                        — <?= date('d/m/Y', strtotime($fecha['fecha_fin'])) ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <p style="margin:0;color:#6b21a8;font-size:13px;">
                Tipo: <?= ucfirst($fecha['tipo'] ?? 'calendario') ?>
            </p>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Revisa que los comercios asociados a esta fecha tengan ofertas actualizadas.
</p>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/fechas" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Gestionar fechas
    </a>
</p>

<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Reseña reportada</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Una reseña en <strong><?= htmlspecialchars($comercio['nombre'] ?? '') ?></strong> ha sido reportada por un usuario.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef3c7;border:1px solid #fde68a;border-radius:6px;margin:0 0 16px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 4px;color:#92400e;font-size:12px;font-weight:bold;">Motivo del reporte:</p>
            <p style="margin:0;color:#78350f;font-size:14px;line-height:1.5;">
                <?= htmlspecialchars($reporte['motivo'] ?? '') ?>
            </p>
            <?php if (!empty($reporte['descripcion'])): ?>
                <p style="margin:8px 0 0;color:#78350f;font-size:13px;line-height:1.5;">
                    <?= htmlspecialchars($reporte['descripcion']) ?>
                </p>
            <?php endif; ?>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 4px;color:#94a3b8;font-size:12px;">Reseña reportada:</p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                Por: <strong><?= htmlspecialchars($resena['nombre_autor'] ?? '') ?></strong> —
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?= $i <= (int)($resena['calificacion'] ?? 0) ? '&#9733;' : '&#9734;' ?>
                <?php endfor; ?>
            </p>
            <p style="margin:0;color:#475569;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars(mb_substr($resena['comentario'] ?? '', 0, 300)) ?>"
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/resenas/reportes" style="display:inline-block;background:#dc2626;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Revisar reportes
    </a>
</p>

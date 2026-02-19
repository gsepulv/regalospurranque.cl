<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Nueva reseña recibida</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha recibido una nueva reseña para <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> que requiere moderación.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Autor:</strong> <?= htmlspecialchars($resena['nombre_autor']) ?>
                <?php if (!empty($resena['email_autor'])): ?>
                    (<?= htmlspecialchars($resena['email_autor']) ?>)
                <?php endif; ?>
            </p>
            <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                <strong>Calificación:</strong>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?= $i <= (int)$resena['calificacion'] ? '&#9733;' : '&#9734;' ?>
                <?php endfor; ?>
                (<?= (int)$resena['calificacion'] ?>/5)
            </p>
            <p style="margin:0;color:#334155;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars(mb_substr($resena['comentario'] ?? '', 0, 300)) ?><?= mb_strlen($resena['comentario'] ?? '') > 300 ? '...' : '' ?>"
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/resenas" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Moderar reseña
    </a>
</p>

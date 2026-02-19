<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Tu reseña ha sido aprobada</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($resena['nombre_autor']) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Tu reseña para <strong><?= htmlspecialchars($comercio['nombre'] ?? '') ?></strong> ha sido aprobada y ya es visible en nuestro directorio.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;color:#15803d;font-size:13px;">
                <strong>Tu calificación:</strong>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?= $i <= (int)$resena['calificacion'] ? '&#9733;' : '&#9734;' ?>
                <?php endfor; ?>
            </p>
            <p style="margin:0;color:#166534;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars(mb_substr($resena['comentario'] ?? '', 0, 200)) ?>"
            </p>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Gracias por compartir tu experiencia con la comunidad de Purranque.
</p>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver reseña publicada
    </a>
</p>

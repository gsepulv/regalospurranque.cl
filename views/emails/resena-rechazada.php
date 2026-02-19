<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Sobre tu reseña</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($resena['nombre_autor']) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Lamentablemente tu reseña para <strong><?= htmlspecialchars($comercio['nombre'] ?? '') ?></strong> no pudo ser publicada
    porque no cumple con nuestras normas de la comunidad.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0;color:#991b1b;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars(mb_substr($resena['comentario'] ?? '', 0, 200)) ?>"
            </p>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Te invitamos a enviar una nueva reseña que cumpla con nuestras normas: opiniones respetuosas,
    basadas en experiencias reales y sin contenido ofensivo.
</p>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Escribir nueva reseña
    </a>
</p>

<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Han respondido a tu reseña</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($resena['nombre_autor']) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    <strong><?= htmlspecialchars($comercio['nombre'] ?? '') ?></strong> ha respondido a tu reseña:
</p>

<!-- Reseña original -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 12px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 4px;color:#94a3b8;font-size:12px;">Tu reseña:</p>
            <p style="margin:0;color:#475569;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars(mb_substr($resena['comentario'] ?? '', 0, 200)) ?>"
            </p>
        </td>
    </tr>
</table>

<!-- Respuesta -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 4px;color:#2563eb;font-size:12px;font-weight:bold;">Respuesta del comercio:</p>
            <p style="margin:0;color:#1e40af;font-size:14px;line-height:1.5;">
                "<?= htmlspecialchars($respuesta) ?>"
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver en el sitio
    </a>
</p>

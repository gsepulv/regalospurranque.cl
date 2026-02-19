<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Bienvenido a <?= htmlspecialchars($siteName) ?></h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($comercio['nombre']) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Tu comercio ha sido registrado exitosamente en nuestro directorio comercial de Purranque.
    A partir de ahora, los vecinos y visitantes de la comuna podrán encontrarte fácilmente.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 12px;font-size:15px;font-weight:bold;color:#15803d;">Tu perfil incluye:</p>
            <p style="margin:0 0 6px;color:#166534;font-size:14px;">&#10003; Ficha de tu comercio con datos de contacto</p>
            <p style="margin:0 0 6px;color:#166534;font-size:14px;">&#10003; Ubicación en el mapa de Purranque</p>
            <p style="margin:0 0 6px;color:#166534;font-size:14px;">&#10003; Visibilidad en categorías y fechas especiales</p>
            <p style="margin:0;color:#166534;font-size:14px;">&#10003; Reseñas y calificaciónes de clientes</p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver tu perfil
    </a>
</p>

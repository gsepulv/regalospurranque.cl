<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Tu comercio ha sido aprobado</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Tu comercio <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> ha sido revisado y aprobado.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0;color:#15803d;font-size:15px;font-weight:bold;">
                &#10003; Ya eres parte del directorio comercial de Purranque
            </p>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 20px;line-height:1.6;">
    A partir de ahora tu comercio es visible para los vecinos y visitantes de la comuna.
</p>

<p style="text-align:center;margin:0 0 12px;">
    <a href="<?= $siteUrl ?>/comercio/<?= htmlspecialchars($comercio['slug'] ?? '') ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver mi perfil
    </a>
</p>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/mi-comercio/login" style="color:#2563eb;font-size:14px;text-decoration:underline;">
        Acceder a mi panel
    </a>
</p>

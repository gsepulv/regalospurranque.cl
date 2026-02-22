<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Sobre tu comercio en <?= htmlspecialchars($siteName) ?></h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Tu comercio <strong><?= htmlspecialchars($comercio['nombre']) ?></strong> ha sido revisado, pero no ha podido ser aprobado en este momento.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <?php if (!empty($motivo)): ?>
                <p style="margin:0 0 8px;color:#9a3412;font-size:13px;font-weight:bold;">Motivo:</p>
                <p style="margin:0;color:#9a3412;font-size:14px;line-height:1.5;">
                    <?= htmlspecialchars($motivo) ?>
                </p>
            <?php else: ?>
                <p style="margin:0;color:#9a3412;font-size:14px;line-height:1.5;">
                    La informaci&oacute;n proporcionada necesita ser revisada o completada para cumplir con los requisitos del directorio.
                </p>
            <?php endif; ?>
        </td>
    </tr>
</table>

<p style="color:#475569;margin:0 0 20px;line-height:1.6;">
    Puedes corregir la informaci&oacute;n y contactarnos para que revisemos nuevamente tu solicitud.
</p>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/contacto" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ir al formulario de contacto
    </a>
</p>

<p style="color:#94a3b8;font-size:13px;margin:0;line-height:1.5;">
    📧 Si no encuentras este correo en tu bandeja de entrada, revisa tu carpeta de Spam o Correo no deseado. El mensaje puede tardar unos minutos en llegar.
</p>

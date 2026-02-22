<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Recuperar contraseña</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($nombre) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <?= htmlspecialchars($siteName) ?>.
</p>

<p style="text-align:center;margin:0 0 20px;">
    <a href="<?= htmlspecialchars($resetUrl) ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Restablecer contraseña
    </a>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef3c7;border:1px solid #fde68a;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0 0 6px;color:#92400e;font-size:13px;">&#9202; Este enlace expira en <strong>1 hora</strong>.</p>
            <p style="margin:0;color:#92400e;font-size:13px;">Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña no será modificada.</p>
        </td>
    </tr>
</table>

<p style="color:#94a3b8;font-size:12px;margin:0 0 12px;line-height:1.5;">
    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
    <a href="<?= htmlspecialchars($resetUrl) ?>" style="color:#2563eb;word-break:break-all;"><?= htmlspecialchars($resetUrl) ?></a>
</p>

<p style="color:#94a3b8;font-size:13px;margin:0;line-height:1.5;">
    📧 Si no encuentras este correo en tu bandeja de entrada, revisa tu carpeta de Spam o Correo no deseado. El mensaje puede tardar unos minutos en llegar.
</p>

<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Tu plan est&aacute; por vencer</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($usuario_nombre) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    El plan <strong><?= htmlspecialchars($comercio['plan']) ?></strong> de tu comercio
    <strong><?= htmlspecialchars($comercio['nombre']) ?></strong>
    <?php if ($diasRestantes === 1): ?>
        vence <strong>ma&ntilde;ana</strong> (<?= date('d/m/Y', strtotime($fechaVencimiento)) ?>).
    <?php elseif ($diasRestantes === 0): ?>
        vence <strong>hoy</strong>.
    <?php else: ?>
        vence en <strong><?= $diasRestantes ?> d&iacute;as</strong> (<?= date('d/m/Y', strtotime($fechaVencimiento)) ?>).
    <?php endif; ?>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:<?= $diasRestantes <= 1 ? '#fef2f2' : '#fffbeb' ?>;border:1px solid <?= $diasRestantes <= 1 ? '#fecaca' : '#fde68a' ?>;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 6px;color:<?= $diasRestantes <= 1 ? '#991b1b' : '#92400e' ?>;font-size:14px;">
                Si tu plan vence sin renovaci&oacute;n, tu comercio dejar&aacute; de aparecer en el directorio.
            </p>
            <p style="margin:0;color:<?= $diasRestantes <= 1 ? '#991b1b' : '#92400e' ?>;font-size:14px;">
                Ingresa a tu panel para solicitar la renovaci&oacute;n.
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/mi-comercio" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ir a mi comercio
    </a>
</p>

<p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">
    Si ya renovaste tu plan, puedes ignorar este mensaje.
</p>

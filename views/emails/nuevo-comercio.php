<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Nuevo comercio registrado</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Se ha registrado un nuevo comercio en el directorio:
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 20px;">
    <tr>
        <td style="padding:16px;">
            <p style="margin:0 0 8px;font-size:18px;font-weight:bold;color:#1e293b;">
                <?= htmlspecialchars($comercio['nombre']) ?>
            </p>
            <?php if (!empty($comercio['direccion'])): ?>
                <p style="margin:0 0 6px;color:#64748b;font-size:13px;">
                    &#128205; <?= htmlspecialchars($comercio['direccion']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($comercio['telefono'])): ?>
                <p style="margin:0 0 6px;color:#64748b;font-size:13px;">
                    &#128222; <?= htmlspecialchars($comercio['telefono']) ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($comercio['email'])): ?>
                <p style="margin:0 0 6px;color:#64748b;font-size:13px;">
                    &#9993; <?= htmlspecialchars($comercio['email']) ?>
                </p>
            <?php endif; ?>
            <p style="margin:8px 0 0;color:#64748b;font-size:13px;">
                <strong>Plan:</strong> <?= ucfirst($comercio['plan'] ?? 'basico') ?> |
                <strong>Estado:</strong> <?= ($comercio['activo'] ?? 1) ? 'Activo' : 'Inactivo' ?>
            </p>
        </td>
    </tr>
</table>

<p style="text-align:center;margin:0;">
    <a href="<?= $siteUrl ?>/admin/comercios/editar/<?= (int)($comercio['id'] ?? 0) ?>" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver en el admin
    </a>
</p>

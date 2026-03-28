<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Comercios pr&oacute;ximos a vencer</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Los siguientes comercios tienen su plan por vencer en los pr&oacute;ximos 7 d&iacute;as:
</p>

<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin:0 0 20px;font-size:14px;">
    <thead>
        <tr style="background:#f1f5f9;">
            <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #e2e8f0;color:#475569;">Comercio</th>
            <th style="text-align:left;padding:10px 8px;border-bottom:2px solid #e2e8f0;color:#475569;">Plan</th>
            <th style="text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0;color:#475569;">Vence</th>
            <th style="text-align:center;padding:10px 8px;border-bottom:2px solid #e2e8f0;color:#475569;">D&iacute;as</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($comercios as $c): ?>
            <?php
                $color = '#475569';
                $bg = 'transparent';
                if ((int)$c['dias_restantes'] <= 1) {
                    $color = '#991b1b';
                    $bg = '#fef2f2';
                } elseif ((int)$c['dias_restantes'] <= 3) {
                    $color = '#92400e';
                    $bg = '#fffbeb';
                }
            ?>
            <tr style="background:<?= $bg ?>;">
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;">
                    <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                </td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;">
                    <?= htmlspecialchars($c['plan']) ?>
                </td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;">
                    <?= date('d/m/Y', strtotime($c['plan_fin'])) ?>
                </td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;color:<?= $color ?>;font-weight:bold;">
                    <?= (int)$c['dias_restantes'] === 0 ? 'HOY' : $c['dias_restantes'] . 'd' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p style="text-align:center;margin:0 0 16px;">
    <a href="<?= $siteUrl ?>/admin/renovaciones" style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Ver renovaciones
    </a>
</p>

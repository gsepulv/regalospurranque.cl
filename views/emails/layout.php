<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:#2563eb;padding:24px 32px;text-align:center;">
                            <a href="<?= $siteUrl ?>" style="color:#ffffff;text-decoration:none;font-size:22px;font-weight:bold;">
                                <?= htmlspecialchars($siteName) ?>
                            </a>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding:32px;">
                            <?= $emailContent ?>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#94a3b8;">
                            <p style="margin:0 0 8px;">
                                Este email fue enviado por <a href="<?= $siteUrl ?>" style="color:#2563eb;text-decoration:none;"><?= htmlspecialchars($siteName) ?></a>
                            </p>
                            <p style="margin:0;">
                                &copy; <?= $year ?> <?= htmlspecialchars($siteName) ?> — Purranque, Chile
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

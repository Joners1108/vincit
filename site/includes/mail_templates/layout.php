<?php
/**
 * Layout HTML transacional — variáveis esperadas (definidas pelo mail_templates.php):
 * @var string $mailPreheader
 * @var string $mailHeadline
 * @var string $mailBodyInner
 * @var string $mailSiteName   escapado
 * @var string $mailSiteUrl    escapado
 * @var string $mailContactEmail escapado
 */
$pre = trim((string) ($mailPreheader ?? ''));
$head = trim((string) ($mailHeadline ?? ''));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $head !== '' ? $mailHeadline : $mailSiteName; ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
<?php if ($pre !== ''): ?>
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;"><?php echo $pre; ?></div>
<?php endif; ?>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                <tr>
                    <td style="background-color:#0a0a0a;padding:20px 28px;text-align:center;border-bottom:3px solid #d4af37;">
                        <a href="<?php echo $mailSiteUrl; ?>/" style="color:#d4af37;text-decoration:none;font-size:22px;font-weight:800;letter-spacing:0.06em;"><?php echo $mailSiteName; ?></a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 28px;color:#1e293b;font-size:16px;line-height:1.6;">
                        <?php if ($head !== ''): ?>
                        <h1 style="margin:0 0 20px;font-size:22px;font-weight:700;color:#0a0a0a;"><?php echo $mailHeadline; ?></h1>
                        <?php endif; ?>
                        <div style="color:#334155;">
                            <?php echo $mailBodyInner ?? ''; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px;background-color:#f8fafc;border-top:1px solid #e2e8f0;font-size:13px;color:#64748b;line-height:1.5;">
                        <p style="margin:0 0 8px;">Precisas de ajuda? Responde a este email ou contacta <a href="mailto:<?php echo $mailContactEmail; ?>" style="color:#b8962e;"><?php echo $mailContactEmail; ?></a></p>
                        <p style="margin:0 0 12px;"><a href="<?php echo $mailSiteUrl; ?>/" style="color:#b8962e;text-decoration:none;">Visitar a loja</a>
                            · <a href="<?php echo $mailSiteUrl; ?>/conta" style="color:#b8962e;text-decoration:none;">A minha conta</a></p>
                        <p style="margin:0;font-size:12px;color:#94a3b8;">© <?php echo date('Y'); ?> <?php echo $mailSiteName; ?>. Email automático, por favor não respondas se não for necessário.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

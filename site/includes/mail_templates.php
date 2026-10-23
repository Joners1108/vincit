<?php
/**
 * Templates HTML para emails transacionais (Vincit).
 * Requer config.php carregado (SITE_NAME, SITE_URL, …).
 */

if (!defined('SITE_NAME')) {
    return;
}

function vincit_mail_h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * @return array{site_name: string, site_url: string, contact_email: string}
 */
function vincit_mail_brand(): array {
    return [
        'site_name' => SITE_NAME,
        'site_url' => rtrim(SITE_URL, '/'),
        'contact_email' => function_exists('vincit_site_email') ? vincit_site_email() : SITE_EMAIL,
    ];
}

/**
 * Compõe o HTML completo do email (layout + corpo).
 */
function vincit_mail_layout_html(string $headline, string $preheader, string $bodyInnerHtml): string {
    $b = vincit_mail_brand();
    $mailPreheader = vincit_mail_h($preheader);
    $mailHeadline = vincit_mail_h($headline);
    $mailBodyInner = $bodyInnerHtml;
    $mailSiteName = vincit_mail_h($b['site_name']);
    $mailSiteUrl = vincit_mail_h($b['site_url']);
    $mailContactEmail = vincit_mail_h($b['contact_email']);
    ob_start();
    include __DIR__ . '/mail_templates/layout.php';
    return (string) ob_get_clean();
}

/** @deprecated Usa vincit_mail_layout_html com título; mantido para compat. */
function vincit_mail_wrap_html(string $innerHtml): string {
    return vincit_mail_layout_html('', '', $innerHtml);
}

function vincit_mail_button(string $url, string $label): string {
    $u = vincit_mail_h($url);
    $l = vincit_mail_h($label);
    return '<table role="presentation" cellspacing="0" cellpadding="0" style="margin:24px 0;"><tr><td style="border-radius:8px;background-color:#d4af37;">'
        . '<a href="' . $u . '" style="display:inline-block;padding:14px 28px;color:#0a0a0a;font-weight:700;text-decoration:none;font-size:15px;">' . $l . '</a>'
        . '</td></tr></table>';
}

function vincit_mail_paragraph(string $html): string {
    return '<p style="margin:0 0 16px;">' . $html . '</p>';
}

function vincit_mail_muted_box(string $html): string {
    return '<div style="margin:20px 0;padding:16px;background-color:#f8fafc;border-radius:8px;border-left:4px solid #d4af37;font-size:14px;color:#475569;">' . $html . '</div>';
}

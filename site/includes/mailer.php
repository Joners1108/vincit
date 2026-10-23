<?php
/**
 * Envio de email — por ordem:
 * 1) Resend (API HTTP, sem PHPMailer nem SMTP) se RESEND_API_KEY estiver definido
 * 2) PHPMailer + SMTP se MAIL_HOST e MAIL_USER estiverem definidos
 * 3) mail() nativo do PHP (sem dependências; no Laragon muitas vezes não envia para fora)
 *
 * Layout HTML: mail_templates.php + mail_templates/layout.php
 */

require_once __DIR__ . '/mail_templates.php';

function vincit_mail_uses_resend(): bool {
    return trim((string) env('RESEND_API_KEY', '')) !== '';
}

function vincit_mail_uses_smtp(): bool {
    return (string) env('MAIL_HOST', '') !== '' && (string) env('MAIL_USER', '') !== '';
}

/**
 * Envio via Resend (https://resend.com) — só precisas de API key, não de password SMTP.
 */
function vincit_send_email_via_resend(string $to, string $subject, string $htmlBody): bool {
    $apiKey = trim((string) env('RESEND_API_KEY', ''));
    if ($apiKey === '') {
        return false;
    }

    $fromRaw = trim((string) env('RESEND_FROM', ''));
    if ($fromRaw === '') {
        $addr = env('MAIL_FROM', function_exists('vincit_site_email') ? vincit_site_email() : SITE_EMAIL);
        $name = env('MAIL_FROM_NAME', SITE_NAME);
        $fromRaw = $name !== '' ? ($name . ' <' . $addr . '>') : $addr;
    }

    $payload = [
        'from' => $fromRaw,
        'to' => [$to],
        'subject' => $subject,
        'html' => $htmlBody,
    ];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return true;
        }
        error_log('Vincit Resend HTTP ' . $code . ': ' . substr((string) $response, 0, 500));
        return false;
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Authorization: Bearer {$apiKey}\r\nContent-Type: application/json\r\n",
            'content' => $json,
            'timeout' => 20,
        ],
    ]);
    $response = @file_get_contents('https://api.resend.com/emails', false, $ctx);
    if ($response === false) {
        error_log('Vincit Resend: falha em file_get_contents');
        return false;
    }
    $code = 0;
    if (!empty($http_response_header) && preg_match('#HTTP/\S+\s+(\d+)#', $http_response_header[0], $m)) {
        $code = (int) $m[1];
    }
    if ($code >= 200 && $code < 300) {
        return true;
    }
    error_log('Vincit Resend HTTP ' . $code . ': ' . substr($response, 0, 500));
    return false;
}

function vincit_send_email_via_php_mail(string $to, string $subject, string $htmlBody): bool {
    $fromAddrDef = function_exists('vincit_site_email') ? vincit_site_email() : SITE_EMAIL;
    $from = defined('MAIL_FROM') ? MAIL_FROM : $fromAddrDef;
    $headers = 'From: ' . $from . "\r\n";
    $headers .= 'Reply-To: ' . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlBody, $headers);
}

/**
 * @return bool true se o envio foi aceite
 */
function vincit_send_email(string $to, string $subject, string $htmlBody): bool {
    $to = trim($to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('Vincit mail: destino inválido ou vazio — assunto: ' . substr($subject, 0, 80));
        return false;
    }

    if (vincit_mail_uses_resend()) {
        return vincit_send_email_via_resend($to, $subject, $htmlBody);
    }

    if (!vincit_mail_uses_smtp()) {
        return vincit_send_email_via_php_mail($to, $subject, $htmlBody);
    }

    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->CharSet = \PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST');
        $mailPass = env('MAIL_PASS', env('MAIL_PASSWORD', ''));
        $mailUser = env('MAIL_USER');
        if ($mailPass !== '' && $mailUser !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $mailUser;
            $mail->Password = $mailPass;
        } else {
            $mail->SMTPAuth = false;
        }
        $enc = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
        if ($enc === 'ssl') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($enc === 'tls' || $enc === 'starttls') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = '';
        }
        $mail->Port = (int) env('MAIL_PORT', '587');
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => filter_var(env('MAIL_VERIFY_SSL', 'true'), FILTER_VALIDATE_BOOLEAN),
                'verify_peer_name' => filter_var(env('MAIL_VERIFY_SSL', 'true'), FILTER_VALIDATE_BOOLEAN),
                'allow_self_signed' => filter_var(env('MAIL_ALLOW_SELF_SIGNED', 'false'), FILTER_VALIDATE_BOOLEAN),
            ],
        ];

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Debugoutput = static function (string $str, int $level): void {
                error_log('Vincit SMTP: ' . trim($str));
            };
        }

        $fromAddr = env('MAIL_FROM', function_exists('vincit_site_email') ? vincit_site_email() : SITE_EMAIL);
        $fromName = env('MAIL_FROM_NAME', SITE_NAME);
        $mail->setFrom($fromAddr, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('Vincit PHPMailer: ' . $mail->ErrorInfo);
        return false;
    } catch (Throwable $e) {
        error_log('Vincit mail: ' . $e->getMessage());
        return false;
    }
}

/**
 * Envia email com layout Vincit (título visível, pré-cabeçalho para clientes de email).
 */
function vincit_send_html_email(string $to, string $subject, string $headline, string $preheader, string $bodyInnerHtml): bool {
    return vincit_send_email($to, $subject, vincit_mail_layout_html($headline, $preheader, $bodyInnerHtml));
}

function vincit_send_welcome_email(string $email, string $userName): void {
    $loginUrl = rtrim(SITE_URL, '/') . '/login';
    $body = vincit_mail_paragraph('Olá <strong>' . vincit_mail_h($userName) . '</strong>,')
        . vincit_mail_paragraph('A tua conta em <strong>' . vincit_mail_h(SITE_NAME) . '</strong> foi criada com sucesso.')
        . vincit_mail_paragraph('Podes iniciar sessão em qualquer altura e completar os teus dados de envio no teu perfil quando fizeres a primeira encomenda.')
        . vincit_mail_button($loginUrl, 'Entrar na loja');
    vincit_send_html_email(
        $email,
        'Conta criada — ' . SITE_NAME,
        'Bem-vindo',
        'A tua conta ' . SITE_NAME . ' está pronta.',
        $body
    );
}

function vincit_order_status_labels(): array {
    return [
        'pending' => 'Pendente',
        'processing' => 'Em processamento',
        'shipped' => 'Enviada',
        'delivered' => 'Entregue',
        'cancelled' => 'Cancelada',
    ];
}

function vincit_customer_order_url(int $orderId): string {
    return rtrim(SITE_URL, '/') . '/conta/encomenda/' . $orderId;
}

/**
 * Bloco HTML com morada, pagamento, linhas de produto e totais (emails transacionais).
 *
 * @param list<array<string, mixed>> $items
 */
function vincit_mail_order_snapshot_html(array $order, array $items): string {
    if (!function_exists('formatPrice')) {
        return '';
    }
    $num = vincit_mail_h((string) ($order['order_number'] ?? ''));
    $shipName = vincit_mail_h(trim((string) ($order['shipping_name'] ?? '')));
    $shipEmail = vincit_mail_h(trim((string) ($order['shipping_email'] ?? '')));
    $phone = trim((string) ($order['shipping_phone'] ?? ''));
    $addr = vincit_mail_h(trim((string) ($order['shipping_address'] ?? '')));
    $city = vincit_mail_h(trim((string) ($order['shipping_city'] ?? '')));
    $cp = vincit_mail_h(trim((string) ($order['shipping_postal_code'] ?? '')));
    $country = vincit_mail_h(trim((string) ($order['shipping_country'] ?? '')));
    $payMethod = vincit_mail_h(vincit_format_order_payment_method($order));
    $payStatus = vincit_mail_h(vincit_format_payment_status_pt((string) ($order['payment_status'] ?? '')));
    $subtotal = formatPrice((float) ($order['subtotal'] ?? 0));
    $shipCost = formatPrice((float) ($order['shipping_cost'] ?? 0));
    $total = formatPrice((float) ($order['total'] ?? 0));
    $notes = trim((string) ($order['notes'] ?? ''));
    $track = trim((string) ($order['tracking_code'] ?? ''));

    $addrBlock = '<strong>Morada de envio</strong><br>'
        . $shipName . '<br>'
        . ($phone !== '' ? vincit_mail_h($phone) . '<br>' : '')
        . $addr . '<br>'
        . $cp . ' ' . $city . '<br>'
        . $country . '<br>'
        . 'Email: ' . $shipEmail;

    $rows = '';
    foreach ($items as $it) {
        $pname = vincit_mail_h((string) ($it['product_name'] ?? ''));
        $sz = !empty($it['size_label']) ? ' <span style="color:#64748b;">(' . vincit_mail_h((string) $it['size_label']) . ')</span>' : '';
        $qty = (int) ($it['quantity'] ?? 0);
        $unit = formatPrice((float) ($it['price'] ?? 0));
        $line = formatPrice((float) ($it['total'] ?? 0));
        $rows .= '<tr>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;vertical-align:top;">' . $pname . $sz . '</td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;text-align:center;">' . $qty . '</td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;text-align:right;white-space:nowrap;">' . $unit . '</td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;text-align:right;white-space:nowrap;">' . $line . '</td>'
            . '</tr>';
    }

    $notesHtml = $notes !== ''
        ? '<p style="margin:16px 0 0;font-size:14px;color:#475569;"><strong>Notas do pedido:</strong><br>' . nl2br(vincit_mail_h($notes)) . '</p>'
        : '';

    $trackHtml = $track !== ''
        ? '<p style="margin:12px 0 0;font-size:14px;"><strong>Código de rastreio:</strong> ' . vincit_mail_h($track) . '</p>'
        : '';

    return '<div style="margin:20px 0;">'
        . vincit_mail_muted_box($addrBlock)
        . '</div>'
        . '<div style="margin:20px 0;padding:16px;background:#f8fafc;border-radius:8px;border-left:4px solid #d4af37;font-size:14px;color:#475569;">'
        . '<strong>Pagamento</strong><br>'
        . 'Estado: ' . $payStatus . '<br>'
        . 'Método: ' . $payMethod
        . '</div>'
        . '<p style="margin:16px 0 8px;font-weight:700;">Produtos</p>'
        . '<table role="presentation" cellspacing="0" cellpadding="0" width="100%" style="border-collapse:collapse;font-size:14px;">'
        . '<thead><tr style="background:#f1f5f9;">'
        . '<th align="left" style="padding:8px;border-bottom:2px solid #cbd5e1;">Artigo</th>'
        . '<th style="padding:8px;border-bottom:2px solid #cbd5e1;">Qtd</th>'
        . '<th align="right" style="padding:8px;border-bottom:2px solid #cbd5e1;">Preço</th>'
        . '<th align="right" style="padding:8px;border-bottom:2px solid #cbd5e1;">Total</th>'
        . '</tr></thead><tbody>' . $rows . '</tbody>'
        . '<tfoot>'
        . '<tr><td colspan="3" align="right" style="padding:10px 8px;"><strong>Subtotal</strong></td><td align="right" style="padding:10px 8px;white-space:nowrap;">' . vincit_mail_h($subtotal) . '</td></tr>'
        . '<tr><td colspan="3" align="right" style="padding:10px 8px;"><strong>Portes</strong></td><td align="right" style="padding:10px 8px;white-space:nowrap;">' . vincit_mail_h($shipCost) . '</td></tr>'
        . '<tr><td colspan="3" align="right" style="padding:10px 8px;"><strong>Total</strong></td><td align="right" style="padding:10px 8px;white-space:nowrap;font-weight:700;">' . vincit_mail_h($total) . '</td></tr>'
        . '</tfoot></table>'
        . $notesHtml
        . $trackHtml
        . '<p style="margin:16px 0 0;font-size:13px;color:#64748b;">Encomenda <strong>#' . $num . '</strong></p>';
}

function vincit_send_order_status_email_to_customer(array $order, string $newStatus, array $items): void {
    $email = trim((string) ($order['shipping_email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $labels = vincit_order_status_labels();
    $label = $labels[$newStatus] ?? $newStatus;
    $num = (string) ($order['order_number'] ?? '');
    $orderId = (int) ($order['id'] ?? 0);
    $detailUrl = $orderId > 0 ? vincit_customer_order_url($orderId) : rtrim(SITE_URL, '/') . '/conta';

    $extra = '';
    if (in_array($newStatus, ['shipped', 'delivered'], true)) {
        $tc = trim((string) ($order['tracking_code'] ?? ''));
        if ($tc !== '') {
            $extra = vincit_mail_paragraph('Utiliza este código para acompanhar o envio: <strong>' . vincit_mail_h($tc) . '</strong>');
        }
    }

    $body = vincit_mail_paragraph('A tua encomenda <strong>#' . vincit_mail_h($num) . '</strong> foi atualizada.')
        . vincit_mail_muted_box('<strong>Novo estado:</strong> ' . vincit_mail_h($label))
        . $extra
        . vincit_mail_order_snapshot_html($order, $items)
        . vincit_mail_paragraph('Vê o detalhe completo na <a href="' . vincit_mail_h($detailUrl) . '" style="color:#b8962e;font-weight:600;">tua conta</a>.')
        . vincit_mail_button($detailUrl, 'Ver encomenda');

    vincit_send_html_email(
        $email,
        'Encomenda #' . $num . ' — ' . $label . ' — ' . SITE_NAME,
        'Estado da encomenda',
        'Encomenda #' . $num . ': ' . $label,
        $body
    );
}

/**
 * @param list<array<string, mixed>> $items
 */
function vincit_send_new_order_paid_admin_email(array $order, array $items): void {
    $orderNumber = (string) ($order['order_number'] ?? '');
    $to = trim((string) env('ADMIN_NOTIFY_EMAIL', function_exists('vincit_site_email') ? vincit_site_email() : SITE_EMAIL));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('Vincit mail: alerta admin encomenda não enviado — ADMIN_NOTIFY_EMAIL inválido (encomenda ' . $orderNumber . ')');
        return;
    }
    $orderId = (int) ($order['id'] ?? 0);
    $adminOrderUrl = $orderId > 0
        ? rtrim(SITE_URL, '/') . '/admin/encomendas/editar/' . $orderId
        : rtrim(SITE_URL, '/') . '/admin/encomendas';

    $cust = vincit_mail_h(trim((string) ($order['shipping_name'] ?? '')));
    $ce = vincit_mail_h(trim((string) ($order['shipping_email'] ?? '')));

    $body = vincit_mail_paragraph('Recebeste um <strong>pagamento confirmado</strong> na loja <strong>' . vincit_mail_h(SITE_NAME) . '</strong>.')
        . vincit_mail_muted_box('<strong>Cliente:</strong> ' . $cust . ' &lt;' . $ce . '&gt;')
        . vincit_mail_order_snapshot_html($order, $items)
        . vincit_mail_paragraph('Gere a encomenda no painel de administração.')
        . vincit_mail_button($adminOrderUrl, 'Abrir encomenda no admin');

    $ok = vincit_send_html_email(
        $to,
        '[' . SITE_NAME . '] Encomenda paga — ' . $orderNumber,
        'Nova encomenda paga',
        'Pagamento confirmado: ' . $orderNumber,
        $body
    );
    if (!$ok) {
        error_log('Vincit mail: falha ao enviar alerta admin encomenda paga — ' . $orderNumber . ' (para: ' . $to . ')');
    }
}

/**
 * Confirmação de encomenda ao cliente (ex.: checkout simulado).
 *
 * @param list<array<string, mixed>> $items
 */
function vincit_send_order_confirmation_customer_email(array $order, array $items): void {
    $customerEmail = trim((string) ($order['shipping_email'] ?? ''));
    if ($customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('Vincit mail: confirmação de encomenda não enviada — email inválido');
        return;
    }
    $orderNumber = (string) ($order['order_number'] ?? '');
    $orderId = (int) ($order['id'] ?? 0);
    $detailUrl = $orderId > 0 ? vincit_customer_order_url($orderId) : rtrim(SITE_URL, '/') . '/conta';

    $body = vincit_mail_paragraph('Obrigado pela tua encomenda na <strong>' . vincit_mail_h(SITE_NAME) . '</strong>.')
        . vincit_mail_paragraph('Segue o resumo do que pediste e a morada onde vamos enviar.')
        . vincit_mail_order_snapshot_html($order, $items)
        . vincit_mail_button($detailUrl, 'Ver encomenda na minha conta');

    $ok = vincit_send_html_email(
        $customerEmail,
        'Confirmação de encomenda #' . $orderNumber . ' — ' . SITE_NAME,
        'Encomenda recebida',
        'Encomenda ' . $orderNumber . ' — resumo e envio.',
        $body
    );
    if (!$ok) {
        error_log('Vincit mail: falha confirmação encomenda cliente — ' . $orderNumber . ' (para: ' . $customerEmail . ')');
    }
}

/**
 * Confirmação de pagamento (Stripe) ao cliente.
 *
 * @param list<array<string, mixed>> $items
 */
function vincit_send_payment_confirmation_customer_email(array $order, array $items): void {
    $customerEmail = trim((string) ($order['shipping_email'] ?? ''));
    if ($customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('Vincit mail: confirmação pagamento não enviada — email inválido');
        return;
    }
    $orderNumber = (string) ($order['order_number'] ?? '');
    $orderId = (int) ($order['id'] ?? 0);
    $detailUrl = $orderId > 0 ? vincit_customer_order_url($orderId) : rtrim(SITE_URL, '/') . '/conta';

    $body = vincit_mail_paragraph('O pagamento da encomenda <strong>#' . vincit_mail_h($orderNumber) . '</strong> foi <strong>recebido com sucesso</strong>.')
        . vincit_mail_paragraph('Obrigado por comprares na <strong>' . vincit_mail_h(SITE_NAME) . '</strong>.')
        . vincit_mail_order_snapshot_html($order, $items)
        . vincit_mail_button($detailUrl, 'Ver encomenda na minha conta');

    $ok = vincit_send_html_email(
        $customerEmail,
        'Pagamento confirmado — encomenda #' . $orderNumber . ' — ' . SITE_NAME,
        'Pagamento recebido',
        'Encomenda ' . $orderNumber . ' paga — detalhes.',
        $body
    );
    if (!$ok) {
        error_log('Vincit mail: falha confirmação pagamento cliente — ' . $orderNumber . ' (para: ' . $customerEmail . ')');
    }
}

/**
 * Pedido de redefinição de password.
 */
function vincit_send_password_reset_email(string $toEmail, string $userName, string $resetLink): void {
    $body = vincit_mail_paragraph('Olá <strong>' . vincit_mail_h($userName) . '</strong>,')
        . vincit_mail_paragraph('Recebemos um pedido para redefinir a password da tua conta em <strong>' . vincit_mail_h(SITE_NAME) . '</strong>.')
        . vincit_mail_button($resetLink, 'Redefinir password')
        . vincit_mail_paragraph('<span style="font-size:14px;color:#64748b;">O link expira em 2 horas. Se não foste tu, ignora este email.</span>');
    vincit_send_html_email(
        $toEmail,
        SITE_NAME . ' — Redefinir password',
        'Redefinir password',
        'Link para criares uma nova password.',
        $body
    );
}

/**
 * Mensagem de contacto do formulário de apoio (para a loja).
 */
function vincit_send_support_inquiry_email(string $shopEmail, string $senderName, string $senderEmail, string $subject, string $message): void {
    $shopEmail = trim($shopEmail);
    if ($shopEmail === '' || !filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
        return;
    }
    $subjectLine = trim(str_replace(["\r", "\n"], ' ', $subject));
    if ($subjectLine === '') {
        $subjectLine = 'Sem assunto';
    }
    $body = vincit_mail_paragraph('Nova mensagem desde o formulário de <strong>Apoio ao Cliente</strong>.')
        . vincit_mail_muted_box(
            '<strong>Nome:</strong> ' . vincit_mail_h($senderName) . '<br>'
            . '<strong>Email:</strong> ' . vincit_mail_h($senderEmail) . '<br>'
            . '<strong>Assunto:</strong> ' . vincit_mail_h($subject)
        )
        . '<div style="margin-top:16px;padding:16px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;white-space:pre-wrap;font-size:14px;">' . vincit_mail_h($message) . '</div>';
    vincit_send_html_email(
        $shopEmail,
        '[Apoio] ' . $subjectLine . ' — ' . SITE_NAME,
        'Mensagem de contacto',
        'De: ' . $senderName,
        $body
    );
}

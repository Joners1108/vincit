<?php
/**
 * Sugestões de morada (Portugal por defeito) via OpenStreetMap Nominatim.
 * Uso: GET ?q=rua+lisboa&country=pt&limit=8
 * Respeita a política de uso: User-Agent com contacto em NOMINATIM_USER_AGENT (.env)
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/env_loader.php';
vincit_load_env_files([
    __DIR__ . '/../../.env',
    __DIR__ . '/../.env',
]);

if (!env_bool('ADDRESS_AUTOCOMPLETE_ENABLED', true)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Autocomplete desativado']);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$qLen = function_exists('mb_strlen') ? mb_strlen($q, 'UTF-8') : strlen($q);
if ($qLen < 3) {
    echo json_encode(['ok' => true, 'results' => []]);
    exit;
}

$country = isset($_GET['country']) ? preg_replace('/[^a-z]/i', '', $_GET['country']) : 'pt';
if ($country === '') {
    $country = 'pt';
}
$limit = isset($_GET['limit']) ? min(12, max(1, (int) $_GET['limit'])) : 8;

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$now = time();
$ip = $_SERVER['REMOTE_ADDR'] ?? '0';
$_SESSION['_addr_req_ip'] = $_SESSION['_addr_req_ip'] ?? [];
$_SESSION['_addr_req_ip'][$ip] = $_SESSION['_addr_req_ip'][$ip] ?? [];
$_SESSION['_addr_req_ip'][$ip] = array_filter($_SESSION['_addr_req_ip'][$ip], function ($t) use ($now) {
    return ($now - $t) < 60;
});
if (count($_SESSION['_addr_req_ip'][$ip]) > 40) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Muitos pedidos. Tenta daqui a um minuto.']);
    exit;
}
$_SESSION['_addr_req_ip'][$ip][] = $now;

$userAgent = env('NOMINATIM_USER_AGENT', 'VincitStore/1.0');
if (strlen($userAgent) < 10) {
    $userAgent = 'VincitStore/1.0 (contacto: ' . env('SITE_EMAIL', 'noreply@localhost') . ')';
}

$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
    'format' => 'json',
    'addressdetails' => 1,
    'limit' => $limit,
    'q' => $q,
    'countrycodes' => strtolower($country),
], '', '&', PHP_QUERY_RFC3986);

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'cURL não disponível no PHP']);
    exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'User-Agent: ' . $userAgent,
        'Accept-Language: pt,en',
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
]);

$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false || $code !== 200) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Serviço de moradas indisponível']);
    exit;
}

$data = json_decode($body, true);
if (!is_array($data)) {
    echo json_encode(['ok' => true, 'results' => []]);
    exit;
}

$results = [];
foreach ($data as $item) {
    $addr = isset($item['address']) && is_array($item['address']) ? $item['address'] : [];
    $line = '';

    if (!empty($addr['road'])) {
        $line = $addr['road'];
        if (!empty($addr['house_number'])) {
            $line .= ', ' . $addr['house_number'];
        }
    } elseif (!empty($addr['pedestrian'])) {
        $line = $addr['pedestrian'];
    } elseif (!empty($addr['path'])) {
        $line = $addr['path'];
    } elseif (!empty($addr['neighbourhood'])) {
        $line = $addr['neighbourhood'];
    }

    $city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? $addr['municipality'] ?? $addr['county'] ?? '';
    $postcode = $addr['postcode'] ?? '';

    if ($line === '' && !empty($item['display_name'])) {
        $parts = explode(',', $item['display_name'], 2);
        $line = trim($parts[0]);
    }

    $results[] = [
        'label' => $item['display_name'] ?? $line,
        'address' => $line,
        'city' => $city,
        'postal_code' => $postcode,
        'country' => $addr['country'] ?? '',
    ];
}

echo json_encode(['ok' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);

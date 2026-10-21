<?php
declare(strict_types=1);
// Only this gateway is public. Browser profiles and worker files live elsewhere.
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
$configFile = dirname(__DIR__, 2) . '/license-gateway.php';
if (!is_file($configFile)) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width, initial-scale=1"><title>License Watch</title><body style="background:#0b1016;color:#e6f5ee;font:18px/1.6 system-ui;margin:12vh auto;padding:24px;max-width:600px"><h1>License Watch.</h1><p>The private checker is awaiting its server connection.</p><p>The portfolio is online. Continuous checking becomes available once the browser worker is connected.</p></body></html>';
    exit;
}
$config = require $configFile;
$upstream = rtrim((string)($config['worker_url'] ?? ''), '/');
$key = (string)($config['gateway_key'] ?? '');
$public = parse_url((string)($config['public_url'] ?? 'https://robrechtvinckier.com/license/'));
$target = parse_url($upstream);
$loopback = in_array($target['host'] ?? '', ['127.0.0.1', 'localhost', '::1'], true);
if (!extension_loaded('curl') || strlen($key) < 32 || !isset($target['host'], $public['host']) || isset($target['user']) || isset($target['pass']) || (($target['scheme'] ?? '') !== 'https' && !$loopback)) {
    http_response_code(503); exit('The checker gateway needs configuration.');
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST'], true)) { http_response_code(405); exit; }
$uri = $_SERVER['REQUEST_URI'] ?? '/license/';
if (!preg_match('#^/license(?:/|\?|$)#', $uri) || strlen($uri) > 2048) { http_response_code(400); exit; }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 26214400) { http_response_code(413); exit; }
$body = $method === 'POST' ? file_get_contents('php://input', false, null, 0, 26214401) : '';
if (strlen((string)$body) > 26214400) { http_response_code(413); exit; }
$host = $public['host'] . (isset($public['port']) ? ':' . $public['port'] : '');
$headers = ['X-License-Public-Host: ' . $host, 'X-License-Gateway: ' . $key];
foreach (['CONTENT_TYPE' => 'Content-Type', 'HTTP_COOKIE' => 'Cookie', 'HTTP_ORIGIN' => 'Origin', 'HTTP_X_CSRF_TOKEN' => 'X-CSRF-Token'] as $source => $name) {
    if (isset($_SERVER[$source]) && !preg_match('/[\r\n]/', $_SERVER[$source])) { $headers[] = $name . ': ' . $_SERVER[$source]; }
}
$handle = curl_init($upstream . $uri);
curl_setopt_array($handle, [
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 70,
    CURLOPT_HEADERFUNCTION => static function ($curl, string $line): int {
        if (preg_match('#^HTTP/\S+ (\d{3})#', $line, $m)) { http_response_code((int)$m[1]); }
        elseif (preg_match('/^(Content-Type|Set-Cookie|Location|Content-Disposition|Content-Security-Policy|X-Frame-Options):/i', $line)) { header(trim($line), false); }
        return strlen($line);
    },
    CURLOPT_WRITEFUNCTION => static function ($curl, string $data): int { echo $data; return strlen($data); },
]);
if ($method === 'POST') { curl_setopt($handle, CURLOPT_POSTFIELDS, $body); }
if (curl_exec($handle) === false) {
    if (!headers_sent()) { http_response_code(502); header('Content-Type: text/plain'); }
    echo 'The browser worker is unavailable. Please retry shortly.';
}
curl_close($handle);

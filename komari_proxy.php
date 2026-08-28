<?php

header('Content-Type: application/json; charset=utf-8');

$path = isset($_GET['path']) ? (string)$_GET['path'] : '';
if (!preg_match('#^/(nodes|recent/[0-9a-fA-F-]{36})$#', $path)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Komari API path']);
    exit;
}

$base_url = getenv('KOMARI_BASE_URL') ?: 'https://server.lris625.top';
$url = rtrim($base_url, '/') . '/api' . $path;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_USERAGENT => 'Homepage Komari Proxy',
]);

$body = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($body === false) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Komari request failed: ' . $error]);
    exit;
}

http_response_code($status >= 200 && $status < 600 ? $status : 502);
echo $body;

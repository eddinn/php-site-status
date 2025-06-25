<?php
require_once 'init.php';

header("Content-Type: text/html; charset=utf-8");

// Input sanitization
$url = $_GET['url'] ?? '';
$url = filter_var($url, FILTER_SANITIZE_URL);

// Validate URL
if (!$url || !preg_match('/^https?:\/\//', $url)) {
    http_response_code(400);
    exit("Invalid or missing URL.");
}

// SSRF protection – resolve IP
$host = parse_url($url, PHP_URL_HOST);
$ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

// Check private/reserved IPs (IPv4 + loopback IPv6)
$blocked_ranges = [
    '10.0.0.0|10.255.255.255',
    '172.16.0.0|172.31.255.255',
    '192.168.0.0|192.168.255.255',
    '127.0.0.0|127.255.255.255',
    '169.254.0.0|169.254.255.255',
    '::1|::1',
];

function ip_in_range($ip, $range) {
    [$start, $end] = explode('|', $range);
    return (inet_pton($ip) >= inet_pton($start) && inet_pton($ip) <= inet_pton($end));
}

foreach ($blocked_ranges as $range) {
    if (ip_in_range($ip, $range)) {
        http_response_code(403);
        exit("Access to private IP range is blocked.");
    }
}

// Fetch content using curl
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_TIMEOUT => 6,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => "HomelabDashboard/1.0",
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response or no content
if ($http_code >= 200 && $http_code < 300 && $response) {
    echo $response;
} else {
    http_response_code(204); // No content
}

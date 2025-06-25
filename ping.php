<?php
// ping.php — receives ?url= and returns the raw page HTML or title

header('Content-Type: text/plain');

if (!isset($_GET['url'])) {
    http_response_code(400);
    exit("Missing URL");
}

$url = filter_var($_GET['url'], FILTER_VALIDATE_URL);
if (!$url) {
    http_response_code(400);
    exit("Invalid URL");
}

// Avoid timeouts and long redirects
set_time_limit(8);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 6,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_NOBODY => false,
    CURLOPT_HEADER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($http_code >= 200 && $http_code < 400 && strpos($content_type, 'text/html') !== false) {
    echo $response;
    exit;
}

http_response_code($http_code);
exit("Unavailable or invalid content");

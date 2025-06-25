<?php
header('Content-Type: application/json');

if (!isset($_GET['url'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing URL"]);
    exit;
}

$url = $_GET['url'];

function checkUrl($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $title = "No Title";

    if ($http_code === 200 && preg_match("/<title>(.*?)<\/title>/i", $response, $matches)) {
        $title = $matches[1];
    }

    curl_close($ch);
    return [$http_code === 200, $title];
}

list($is_online, $title) = checkUrl($url);
echo json_encode([
    'online' => $is_online,
    'title' => $title
]);

<?php
/**
 * PHP Proxy for Google Sheets CSV Fetching
 * Place this in your public_html folder along with index.html
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: text/plain; charset=utf-8");

if (!isset($_GET['url'])) {
    http_response_code(400);
    echo "URL is required";
    exit;
}

$url = $_GET['url'];

// Basic validation to ensure it's a Google Sheets URL
if (strpos($url, 'docs.google.com/spreadsheets') === false) {
    http_response_code(403);
    echo "Forbidden: Only Google Sheets URLs are allowed";
    exit;
}

// Try using cURL first (more common on shared hosting)
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: text/csv,application/csv,text/plain']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $data !== false) {
        echo $data;
        exit;
    }
}

// Fallback to file_get_contents
$options = [
    "http" => [
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n" .
                    "Accept: text/csv,application/csv,text/plain\r\n",
        "timeout" => 10
    ]
];

$context = stream_context_create($options);
$data = @file_get_contents($url, false, $context);

if ($data === false) {
    $error = error_get_last();
    http_response_code(500);
    echo "Error fetching data: " . $error['message'];
} else {
    echo $data;
}
?>

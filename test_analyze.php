<?php
$boundary = "---------------------" . md5(mt_rand() . microtime());
$payload = "";

$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="report"; filename="dummy.png"' . "\r\n";
$payload .= 'Content-Type: image/png' . "\r\n\r\n";
$payload .= base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=") . "\r\n";

$payload .= "--" . $boundary . "--\r\n";

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: multipart/form-data; boundary=' . $boundary
        ],
        'content' => $payload,
        'ignore_errors' => true
    ]
];
$context = stream_context_create($opts);
$response = @file_get_contents('http://127.0.0.1:8081/AI-Nutri-Planner/api/health/analyze.php', false, $context);
file_put_contents(__DIR__ . '/test_output.txt', "Response: " . $response . "\n");

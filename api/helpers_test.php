<?php
require_once __DIR__ . '/config.php';
// TEMPORARY FIX FOR TESTING
function auth_check() {
    return 1;
}

function json_ok($data, $msg = '') {
    echo json_encode(['status' => 'success', 'message' => $msg, 'data' => $data]);
    exit;
}

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

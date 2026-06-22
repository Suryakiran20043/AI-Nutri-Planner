<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_FILES['report'] = [
    'name' => 'dummy.pdf',
    'type' => 'application/pdf',
    'tmp_name' => __DIR__ . '/dummy.pdf',
    'error' => 0,
    'size' => 100
];
file_put_contents(__DIR__ . '/dummy.pdf', 'dummy content');

function auth_check() { return 1; }

ob_start();
register_shutdown_function(function() {
    $out = ob_get_clean();
    file_put_contents(__DIR__ . '/cli_output.txt', $out);
});

require __DIR__ . '/api/health/analyze.php';

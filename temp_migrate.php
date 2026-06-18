<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';

$db = get_db();
try {
    $db->exec("ALTER TABLE users ADD COLUMN mobile_number VARCHAR(20) UNIQUE NULL");
    echo "Added mobile_number column.\n";
} catch (Exception $e) {
    echo "mobile_number column might already exist: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) NULL");
    echo "Added otp_code column.\n";
} catch (Exception $e) {
    echo "otp_code column might already exist: " . $e->getMessage() . "\n";
}

try {
    $db->exec("ALTER TABLE users ADD COLUMN otp_expires_at TIMESTAMP NULL");
    echo "Added otp_expires_at column.\n";
} catch (Exception $e) {
    echo "otp_expires_at column might already exist: " . $e->getMessage() . "\n";
}
echo "Migration complete.\n";

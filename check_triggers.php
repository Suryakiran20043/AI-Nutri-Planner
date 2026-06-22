<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';
$db = get_db();
try {
    $stmt = $db->query("SHOW TRIGGERS FROM nutriplan");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Triggers: " . json_encode($triggers);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

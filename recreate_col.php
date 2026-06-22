<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';
$db = get_db();
try {
    $db->exec("ALTER TABLE meal_plans DROP COLUMN ingredients");
} catch (Exception $e) {}

try {
    $db->exec("ALTER TABLE meal_plans ADD COLUMN ingredients JSON DEFAULT NULL");
    echo "Recreated ingredients column.<br>";
} catch (Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "<br>";
}

try {
    $stmt = $db->query("SHOW COLUMNS FROM meal_plans");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns:<br>";
    foreach($cols as $col) echo $col['Field'] . "<br>";
} catch (Exception $e) {}

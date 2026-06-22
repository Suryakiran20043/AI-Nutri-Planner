<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';

try {
    $db = get_db();
    echo "Connected successfully.<br>";

    // Try to add the column
    $db->exec("ALTER TABLE meal_plans ADD COLUMN ingredients JSON DEFAULT NULL");
    echo "Altered table successfully.<br>";
    
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Check columns
try {
    $stmt = $db->query("SHOW COLUMNS FROM meal_plans");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in meal_plans:<br>";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
} catch (Exception $e) {
    echo "Could not fetch columns: " . $e->getMessage() . "<br>";
}

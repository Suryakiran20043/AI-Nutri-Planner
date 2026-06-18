<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';
$db = get_db();
$q = $db->query("DESCRIBE meal_plans");
print_r($q->fetchAll(PDO::FETCH_ASSOC));

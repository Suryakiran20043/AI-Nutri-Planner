<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) json_error('id is required');

$pdo = get_db();

// Fetch the food record
$stmt = $pdo->prepare('SELECT * FROM etm_foods WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$food = $stmt->fetch();

if (!$food) json_error('Food not found', 404);

// Parse JSON directions into an array
$food['directions'] = json_decode($food['directions'] ?? '[]', true) ?? [];

// Fetch ingredients ordered by sort_order
$stmt = $pdo->prepare('SELECT * FROM etm_food_ingredients WHERE etm_food_id = :food_id ORDER BY sort_order ASC');
$stmt->execute([':food_id' => $id]);
$food['ingredients'] = $stmt->fetchAll();

json_ok($food);

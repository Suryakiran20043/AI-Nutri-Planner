<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$date     = $body['log_date']  ?? date('Y-m-d');
$fdcId    = (int) ($body['fdc_id'] ?? 0);
$foodName = trim($body['food_name'] ?? '');
$calories = (int) ($body['calories'] ?? 0);
$protein  = (float) ($body['protein_g'] ?? -1);
$carbs    = (float) ($body['carbs_g'] ?? -1);
$fat      = (float) ($body['fat_g'] ?? -1);
$quantity = (float) ($body['quantity'] ?? 1);
$unit     = trim($body['unit'] ?? 'serving');

if (!$foodName && !$fdcId) {
  json_error('Food name or FDC ID is required.');
}

// If macros are not provided but FDC ID is, query USDA detailed endpoint to get exact macros
if ($fdcId && ($protein < 0 || $carbs < 0 || $fat < 0)) {
  try {
    $foodData = usda_fetch("/food/{$fdcId}?api_key=" . USDA_API_KEY);
    $nutrients = [];
    foreach (($foodData['foodNutrients'] ?? []) as $n) {
      $id = $n['nutrient']['id'] ?? ($n['nutrientId'] ?? null);
      $nutrients[$id] = $n['amount'] ?? ($n['value'] ?? 0);
    }
    $foodName = $foodName ?: $foodData['description'];
    $calories = round($nutrients[1008] ?? 0);
    $protein  = round($nutrients[1003] ?? 0, 1);
    $carbs    = round($nutrients[1005] ?? 0, 1);
    $fat      = round($nutrients[1004] ?? 0, 1);
    $unit     = ($foodData['servingSize'] ?? 100) . ' ' . ($foodData['servingSizeUnit'] ?? 'g');
  } catch (Exception $e) {
    // If USDA fetch fails, fall back to zero macros instead of failing
    $protein = max(0, $protein);
    $carbs = max(0, $carbs);
    $fat = max(0, $fat);
  }
} else {
  $protein = max(0, $protein);
  $carbs = max(0, $carbs);
  $fat = max(0, $fat);
}

$db = get_db();

try {
  $st = $db->prepare('
    INSERT INTO food_log (user_id, log_date, fdc_id, food_name, calories, protein_g, carbs_g, fat_g, quantity, unit)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ');
  $st->execute([$uid, $date, $fdcId ?: null, $foodName, $calories, $protein, $carbs, $fat, $quantity, $unit]);

  json_ok(['id' => $db->lastInsertId()], 'Food logged successfully');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$date        = $body['date']        ?? date('Y-m-d');
$slot        = $body['slot']        ?? '';
$fdcId       = (int) ($body['fdc_id'] ?? 0);
$foodName    = trim($body['food_name'] ?? '');
$calories    = (int) ($body['calories'] ?? 0);
$protein     = (float) ($body['protein_g'] ?? 0);
$carbs       = (float) ($body['carbs_g'] ?? 0);
$fat         = (float) ($body['fat_g'] ?? 0);
$fiber       = (float) ($body['fiber_g'] ?? 0);
$servingSize = trim($body['serving_size'] ?? '1 serving');

if (!$slot || !$foodName) {
  json_error('Slot and food name are required.');
}

$db = get_db();

try {
  $st = $db->prepare('
    INSERT INTO meal_plans (user_id, plan_date, meal_slot, fdc_id, food_name, calories,
      protein_g, carbs_g, fat_g, fiber_g, serving_size)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
      fdc_id=VALUES(fdc_id), food_name=VALUES(food_name),
      calories=VALUES(calories), protein_g=VALUES(protein_g),
      carbs_g=VALUES(carbs_g), fat_g=VALUES(fat_g),
      fiber_g=VALUES(fiber_g), serving_size=VALUES(serving_size)
  ');
  $st->execute([$uid, $date, $slot, $fdcId, $foodName, $calories, $protein, $carbs, $fat, $fiber, $servingSize]);

  json_ok(['date' => $date, 'slot' => $slot], 'Meal plan saved successfully');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

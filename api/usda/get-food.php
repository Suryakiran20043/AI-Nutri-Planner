<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$fdcId = (int) ($_GET['fdcId'] ?? 0);
if (!$fdcId) json_error('fdcId is required');

$result    = usda_fetch("/food/{$fdcId}?api_key=" . USDA_API_KEY);
$nutrients = [];
foreach (($result['foodNutrients'] ?? []) as $n) {
  $id = $n['nutrient']['id'] ?? ($n['nutrientId'] ?? null);
  $nutrients[$id] = $n['amount'] ?? ($n['value'] ?? 0);
}

$servingVal = $result['servingSize'] ?? 100;
$servingUnit = $result['servingSizeUnit'] ?? 'g';

json_ok([
  'fdcId'       => $result['fdcId'],
  'description' => $result['description'],
  'dataType'    => $result['dataType'] ?? '',
  'calories'    => round($nutrients[1008] ?? 0),
  'protein_g'   => round($nutrients[1003] ?? 0, 1),
  'carbs_g'     => round($nutrients[1005] ?? 0, 1),
  'fat_g'       => round($nutrients[1004] ?? 0, 1),
  'fiber_g'     => round($nutrients[1079] ?? 0, 1),
  'sugar_g'     => round($nutrients[2000] ?? 0, 1),
  'sodium_mg'   => round($nutrients[1093] ?? 0),
  'servingSize' => trim($servingVal . ' ' . $servingUnit),
]);

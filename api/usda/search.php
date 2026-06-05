<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$query    = urlencode(trim($_GET['query'] ?? ''));
$pageSize = (int) ($_GET['pageSize'] ?? 15);
$dataType = $_GET['dataType'] ?? 'Foundation,SR Legacy,Branded';

if (!$query) json_error('Query is required');

$endpoint = "/foods/search?query={$query}&pageSize={$pageSize}&dataType={$dataType}&api_key=" . USDA_API_KEY;
$result   = usda_fetch($endpoint);

$foods = [];
foreach (($result['foods'] ?? []) as $f) {
  // Extract key nutrients
  $nutrients = [];
  foreach (($f['foodNutrients'] ?? []) as $n) {
    $nutrients[$n['nutrientId']] = $n['value'] ?? 0;
  }
  
  $servingVal = $f['servingSize'] ?? 100;
  $servingUnit = $f['servingSizeUnit'] ?? 'g';
  if (empty($f['servingSize']) && !empty($f['householdServedSizeMethod'])) {
    $servingVal = $f['householdServedSizeMethod'];
    $servingUnit = '';
  }

  $foods[] = [
    'fdcId'        => $f['fdcId'],
    'description'  => $f['description'],
    'dataType'     => $f['dataType'] ?? '',
    'brandOwner'   => $f['brandOwner'] ?? '',
    'calories'     => round($nutrients[1008] ?? 0),  // Energy kcal
    'protein_g'    => round($nutrients[1003] ?? 0, 1), // Protein
    'carbs_g'      => round($nutrients[1005] ?? 0, 1), // Carbohydrate
    'fat_g'        => round($nutrients[1004] ?? 0, 1), // Total fat
    'fiber_g'      => round($nutrients[1079] ?? 0, 1), // Fiber
    'sugar_g'      => round($nutrients[2000] ?? 0, 1), // Sugars
    'sodium_mg'    => round($nutrients[1093] ?? 0),  // Sodium
    'servingSize'  => trim($servingVal . ' ' . $servingUnit),
  ];
}

json_ok([
  'totalHits' => $result['totalHits'] ?? 0,
  'foods'     => $foods,
]);

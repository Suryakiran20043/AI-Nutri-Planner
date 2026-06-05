<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$query  = trim($_GET['query'] ?? '');
$cal    = (int) ($_GET['maxCalories'] ?? 0);
$diet   = trim($_GET['diet'] ?? '');

if (!$query) json_error('Query keyword is required');

try {
  $endpoint = "/recipes/complexSearch?query=" . urlencode($query) . "&number=15&addRecipeNutrition=true";
  
  if ($cal > 0) {
    $endpoint .= "&maxCalories=" . $cal;
  }
  
  if ($diet && $diet !== 'anything') {
    $endpoint .= "&diet=" . urlencode($diet);
  }

  $result = spoonacular_fetch($endpoint);
  
  $recipes = [];
  foreach (($result['results'] ?? []) as $r) {
    // Spoonacular complexSearch with addRecipeNutrition=true outputs simple nutrition structures
    $nutrients = [];
    foreach (($r['nutrition']['nutrients'] ?? []) as $n) {
      $nutrients[$n['name']] = $n['amount'];
    }
    
    $recipes[] = [
      'id'          => $r['id'],
      'title'       => $r['title'],
      'image'       => $r['image'] ?? '',
      'calories'    => round($nutrients['Calories'] ?? 0),
      'protein_g'   => round($nutrients['Protein'] ?? 0, 1),
      'carbs_g'     => round($nutrients['Carbohydrates'] ?? 0, 1),
      'fat_g'       => round($nutrients['Fat'] ?? 0, 1),
      'fiber_g'     => round($nutrients['Fiber'] ?? 0, 1),
      'readyInMinutes' => $r['readyInMinutes'] ?? 30,
      'servingSize' => ($r['servings'] ?? 1) . ' serving'
    ];
  }

  json_ok(['recipes' => $recipes]);
} catch (Exception $e) {
  json_error($e->getMessage(), 502);
}

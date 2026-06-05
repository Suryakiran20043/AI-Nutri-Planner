<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) json_error('Recipe ID is required');

try {
  $result = spoonacular_fetch("/recipes/{$id}/information?includeNutrition=true");

  $nutrients = [];
  foreach (($result['nutrition']['nutrients'] ?? []) as $n) {
    $nutrients[$n['name']] = $n['amount'];
  }

  // Extract ingredients list
  $ingredients = [];
  $ingList = [];
  foreach (($result['extendedIngredients'] ?? []) as $ing) {
    $ingredients[] = $ing['original'];
    $ingList[] = "• " . trim($ing['original']);
  }
  $ingText = empty($ingList) ? "" : "INGREDIENTS:\n" . implode("\n", $ingList) . "\n\nINSTRUCTIONS:\n";

  // Parse structured cooking instructions
  $instructions = '';
  if (!empty($result['analyzedInstructions'])) {
    $steps = [];
    foreach (($result['analyzedInstructions'][0]['steps'] ?? []) as $step) {
      $steps[] = $step['number'] . '. ' . $step['step'];
    }
    $instructions = $ingText . implode("\n", $steps);
  } else {
    $instructions = $ingText . strip_tags($result['instructions'] ?? 'Follow standard cooking practices.');
  }

  json_ok([
    'id'           => $result['id'],
    'title'        => $result['title'],
    'image'        => $result['image'] ?? '',
    'calories'     => round($nutrients['Calories'] ?? 0),
    'protein_g'    => round($nutrients['Protein'] ?? 0, 1),
    'carbs_g'      => round($nutrients['Carbohydrates'] ?? 0, 1),
    'fat_g'        => round($nutrients['Fat'] ?? 0, 1),
    'fiber_g'      => round($nutrients['Fiber'] ?? 0, 1),
    'instructions' => $instructions,
    'ingredients'  => $ingredients,
    'readyInMinutes' => $result['readyInMinutes'] ?? 30,
    'servingSize'  => ($result['servings'] ?? 1) . ' serving'
  ]);
} catch (Exception $e) {
  json_error($e->getMessage(), 502);
}

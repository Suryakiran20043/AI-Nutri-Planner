<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);
$date = $body['date'] ?? date('Y-m-d');

$db = get_db();
try {
  $st = $db->prepare('SELECT * FROM user_profiles WHERE user_id=?');
  $st->execute([$uid]);
  $profile = $st->fetch();
  if (!$profile || !$profile['daily_calories']) {
    json_error('Profile not found. Please complete the calculator first.', 400);
  }

  $totalCal = (int) $profile['daily_calories'];
  $dietType = $profile['diet_type'] ?? 'anything';

  $slots = [
    'breakfast' => ['cal' => (int)($totalCal * 0.25), 'query' => 'oatmeal eggs pancake toast breakfast smoothie', 'usda' => 'eggs yogurt oats granola berries milk'],
    'lunch'     => ['cal' => (int)($totalCal * 0.33), 'query' => 'chicken salad wrap rice burger turkey', 'usda' => 'chicken turkey paneer avocado wrap salad'],
    'dinner'    => ['cal' => (int)($totalCal * 0.33), 'query' => 'salmon steak beef pork pasta vegetables stew', 'usda' => 'salmon steak beef pasta tofu dal rice'],
    'snack'     => ['cal' => (int)($totalCal * 0.09), 'query' => 'nuts fruit chips cashews bar cookie apple', 'usda' => 'apple banana almonds cashews nuts fruits'],
  ];

  $insertSt = $db->prepare('
    INSERT INTO meal_plans (user_id, plan_date, meal_slot, fdc_id, food_name, calories,
      protein_g, carbs_g, fat_g, fiber_g, serving_size, image_url, instructions)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
      fdc_id=VALUES(fdc_id), food_name=VALUES(food_name),
      calories=VALUES(calories), protein_g=VALUES(protein_g),
      carbs_g=VALUES(carbs_g), fat_g=VALUES(fat_g),
      fiber_g=VALUES(fiber_g), serving_size=VALUES(serving_size),
      image_url=VALUES(image_url), instructions=VALUES(instructions)
  ');

  $plan = [];
  $useSpoonacular = (SPOONACULAR_API_KEY !== 'YOUR_SPOONACULAR_API_KEY' && !empty(SPOONACULAR_API_KEY));

  foreach ($slots as $slot => $config) {
    // 1. Check lock
    $checkLock = $db->prepare('SELECT * FROM meal_plans WHERE user_id=? AND plan_date=? AND meal_slot=?');
    $checkLock->execute([$uid, $date, $slot]);
    $existing = $checkLock->fetch();
    if ($existing && $existing['is_locked']) {
      $plan[$slot] = [
        'fdc_id'   => $existing['fdc_id'],
        'name'     => $existing['food_name'],
        'calories' => $existing['calories'],
        'protein'  => $existing['protein_g'],
        'carbs'    => $existing['carbs_g'],
        'fat'      => $existing['fat_g'],
        'fiber'    => $existing['fiber_g'],
        'serving'  => $existing['serving_size'],
        'image_url' => $existing['image_url'],
        'instructions' => $existing['instructions'],
        'is_locked' => 1
      ];
      continue;
    }

    $row = null;

    // 2. Try Spoonacular API if configured
    if ($useSpoonacular) {
      try {
        $q = urlencode($config['query']);
        $maxC = $config['cal'] + 200;
        $minC = max(50, $config['cal'] - 200);
        $endpoint = "/recipes/complexSearch?query={$q}&minCalories={$minC}&maxCalories={$maxC}&number=15&addRecipeNutrition=true&addRecipeInformation=true";
        
        if ($dietType !== 'anything') {
          $endpoint .= "&diet=" . urlencode($dietType);
        }

        $result = spoonacular_fetch($endpoint);
        $recipes = $result['results'] ?? [];

        if (!empty($recipes)) {
          // Shuffle and pick random candidates for recipe variety
          $candidates = [];
          foreach ($recipes as $r) {
            $nutrients = [];
            foreach (($r['nutrition']['nutrients'] ?? []) as $n) {
              $nutrients[$n['name']] = $n['amount'];
            }
            $cal  = round($nutrients['Calories'] ?? 0);
            if (abs($cal - $config['cal']) < 300) {
              $candidates[] = ['recipe' => $r, 'nutrients' => $nutrients];
            }
          }

          $chosen = !empty($candidates) ? $candidates[array_rand($candidates)] : null;
          if ($chosen) {
            $best = $chosen['recipe'];
            $bestNutrients = $chosen['nutrients'];

            // Get prep steps and ingredients
            $ingList = [];
            if (!empty($best['extendedIngredients'])) {
              foreach ($best['extendedIngredients'] as $eing) {
                $ingList[] = "• " . trim($eing['original']);
              }
            }
            $ingText = empty($ingList) ? "" : "INGREDIENTS:\n" . implode("\n", $ingList) . "\n\nINSTRUCTIONS:\n";

            $steps = [];
            if (!empty($best['analyzedInstructions'])) {
              foreach (($best['analyzedInstructions'][0]['steps'] ?? []) as $step) {
                $steps[] = $step['number'] . '. ' . $step['step'];
              }
            }
            $inst = $ingText . (implode("\n", $steps) ?: strip_tags($best['instructions'] ?? 'Follow standard preparation procedures.'));

            $row = [
              'fdc_id'   => $best['id'],
              'name'     => $best['title'],
              'calories' => round($bestNutrients['Calories'] ?? 0),
              'protein'  => round($bestNutrients['Protein'] ?? 0, 1),
              'carbs'    => round($bestNutrients['Carbohydrates'] ?? 0, 1),
              'fat'      => round($bestNutrients['Fat'] ?? 0, 1),
              'fiber'    => round($bestNutrients['Fiber'] ?? 0, 1),
              'serving'  => ($best['servings'] ?? 1) . ' serving',
              'image_url' => $best['image'] ?? null,
              'instructions' => $inst
            ];
          }
        }
      } catch (Exception $e) {
        // Fall through to USDA fallback automatically
      }
    }

    // 3. Fallback to USDA FoodData Central API
    if ($row === null) {
      $query  = urlencode($config['usda']);
      $result = usda_fetch("/foods/search?query={$query}&pageSize=20&dataType=Foundation,SR Legacy&api_key=" . USDA_API_KEY);
      $foods  = $result['foods'] ?? [];

      // Randomize the foods
      shuffle($foods);

      $candidates = [];
      foreach ($foods as $f) {
        $nutrients = [];
        foreach (($f['foodNutrients'] ?? []) as $n) {
          $nutrients[$n['nutrientId']] = $n['value'] ?? 0;
        }
        $cal = round($nutrients[1008] ?? 0);
        
        // Skip zero or near-zero calorie items
        if ($cal <= 10) {
          continue;
        }

        // Calculate scaling factor to reach slot target calories
        $factor = $config['cal'] / $cal;

        // Allow scaling if the factor is reasonable (0.1 to 12.0)
        if ($factor >= 0.1 && $factor <= 12.0) {
          $candidates[] = [
            'food' => $f,
            'nutrients' => $nutrients,
            'factor' => $factor
          ];
        }
      }

      // Fallback if no matching foods in window
      if (empty($candidates)) {
        foreach ($foods as $f) {
          $nutrients = [];
          foreach (($f['foodNutrients'] ?? []) as $n) {
            $nutrients[$n['nutrientId']] = $n['value'] ?? 0;
          }
          $cal = round($nutrients[1008] ?? 0);
          if ($cal > 0) {
            $candidates[] = [
              'food' => $f,
              'nutrients' => $nutrients,
              'factor' => $config['cal'] / $cal
            ];
          }
        }
      }

      $chosen = !empty($candidates) ? $candidates[array_rand($candidates)] : null;
      if ($chosen) {
        $best = $chosen['food'];
        $bestNutrients = $chosen['nutrients'];
        $factor = $chosen['factor'];

        $servingVal = $best['servingSize'] ?? 100;
        $servingUnit = $best['servingSizeUnit'] ?? 'g';
        
        $scaledCal  = round($bestNutrients[1008] * $factor);
        $scaledProt = round(($bestNutrients[1003] ?? 0) * $factor, 1);
        $scaledCarb = round(($bestNutrients[1005] ?? 0) * $factor, 1);
        $scaledFat  = round(($bestNutrients[1004] ?? 0) * $factor, 1);
        $scaledFib  = round(($bestNutrients[1079] ?? 0) * $factor, 1);
        
        $scaledServingVal = round($servingVal * $factor);
        
        if (abs($factor - 1.0) < 0.15) {
          $servingText = trim($servingVal . ' ' . $servingUnit);
        } else {
          $roundedFactor = round($factor, 1);
          $servingText = trim($roundedFactor . ' serv (' . $scaledServingVal . ' ' . $servingUnit . ')');
        }

        $row = [
          'fdc_id'   => $best['fdcId'],
          'name'     => $best['description'],
          'calories' => $scaledCal,
          'protein'  => $scaledProt,
          'carbs'    => $scaledCarb,
          'fat'      => $scaledFat,
          'fiber'    => $scaledFib,
          'serving'  => $servingText,
          'image_url' => null,
          'instructions' => 'Eat fresh as served.'
        ];
      }
    }

    if ($row) {
      $insertSt->execute([
        $uid, $date, $slot,
        $row['fdc_id'], $row['name'], $row['calories'],
        $row['protein'], $row['carbs'], $row['fat'], $row['fiber'], $row['serving'],
        $row['image_url'], $row['instructions']
      ]);
      $plan[$slot] = $row;
    }
  }

  json_ok(['date' => $date, 'plan' => $plan, 'spoonacular_active' => $useSpoonacular]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

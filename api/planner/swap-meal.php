<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$action = $body['action'] ?? 'replace'; // 'replace', 'toggle_lock', or 'regenerate'
$date   = $body['date']   ?? date('Y-m-d');
$slot   = $body['slot']   ?? '';

if (!$slot) json_error('Slot is required');

$db = get_db();
$useSpoonacular = (SPOONACULAR_API_KEY !== 'YOUR_SPOONACULAR_API_KEY' && !empty(SPOONACULAR_API_KEY));

try {
  if ($action === 'toggle_lock') {
    // Check lock state
    $st = $db->prepare('SELECT is_locked FROM meal_plans WHERE user_id=? AND plan_date=? AND meal_slot=?');
    $st->execute([$uid, $date, $slot]);
    $row = $st->fetch();
    
    if (!$row) {
      json_error('No meal found in this slot to lock/unlock.');
    }
    
    $newLock = $row['is_locked'] ? 0 : 1;
    $db->prepare('UPDATE meal_plans SET is_locked=? WHERE user_id=? AND plan_date=? AND meal_slot=?')
       ->execute([$newLock, $uid, $date, $slot]);
       
    json_ok(['is_locked' => $newLock], 'Lock toggled');
  } 
  
  if ($action === 'replace') {
    // Replaces a slot with a specific food detail
    $fdcId        = (int) ($body['fdc_id'] ?? 0);
    $foodName     = trim($body['food_name'] ?? '');
    $calories     = (int) ($body['calories'] ?? 0);
    $protein      = (float) ($body['protein_g'] ?? 0);
    $carbs        = (float) ($body['carbs_g'] ?? 0);
    $fat          = (float) ($body['fat_g'] ?? 0);
    $fiber        = (float) ($body['fiber_g'] ?? 0);
    $servingSize  = trim($body['serving_size'] ?? '1 serving');
    $imageUrl     = trim($body['image_url'] ?? '');
    $instructions = trim($body['instructions'] ?? '');

    if (!$fdcId || !$foodName) {
      json_error('Food details (fdc_id and food_name) are required.');
    }

    $st = $db->prepare('
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
    $st->execute([$uid, $date, $slot, $fdcId, $foodName, $calories, $protein, $carbs, $fat, $fiber, $servingSize, $imageUrl ?: null, $instructions ?: null]);

    json_ok([
      'date' => $date,
      'slot' => $slot,
      'meal' => [
        'fdc_id'   => $fdcId,
        'name'     => $foodName,
        'calories' => $calories,
        'protein'  => $protein,
        'carbs'    => $carbs,
        'fat'      => $fat,
        'fiber'    => $fiber,
        'serving'  => $servingSize,
        'image_url' => $imageUrl,
        'instructions' => $instructions,
        'is_locked' => 0
      ]
    ], 'Meal replaced successfully');
  }

  if ($action === 'regenerate') {
    // Fetch profile
    $st = $db->prepare('SELECT * FROM user_profiles WHERE user_id=?');
    $st->execute([$uid]);
    $profile = $st->fetch();
    if (!$profile || !$profile['daily_calories']) {
      json_error('Profile targets not set. Please complete the calculator first.');
    }

    $totalCal = (int) $profile['daily_calories'];
    $dietType = $profile['diet_type'] ?? 'anything';

    $slots = [
      'breakfast' => ['cal' => (int)($totalCal * 0.25), 'query' => 'eggs muffin breakfast waffle fruit waffle granola', 'usda' => 'eggs yogurt oats granola berries milk'],
      'lunch'     => ['cal' => (int)($totalCal * 0.33), 'query' => 'chicken flatbread wrap soup tacos quesadilla', 'usda' => 'chicken turkey paneer avocado wrap salad'],
      'dinner'    => ['cal' => (int)($totalCal * 0.33), 'query' => 'steak ribs pork fish lobster cod stew curry rice', 'usda' => 'salmon steak beef pasta tofu dal rice'],
      'snack'     => ['cal' => (int)($totalCal * 0.09), 'query' => 'smoothie shake cookie chocolate nut snack banana apple', 'usda' => 'apple banana almonds cashews nuts fruits'],
    ];

    $config = $slots[$slot] ?? ['cal' => 500, 'query' => 'snack', 'usda' => 'food'];
    $row = null;

    if ($useSpoonacular) {
      try {
        $q = urlencode($config['query']);
        $maxC = $config['cal'] + 200;
        $minC = max(50, $config['cal'] - 150);
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
            if (abs($cal - $config['cal']) < 250) {
              $candidates[] = ['recipe' => $r, 'nutrients' => $nutrients];
            }
          }

          $chosen = !empty($candidates) ? $candidates[array_rand($candidates)] : null;
          if ($chosen) {
            $best = $chosen['recipe'];
            $bestNutrients = $chosen['nutrients'];

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
        // Fall back to USDA
      }
    }

    // USDA fallback generator
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
      $ins = $db->prepare('
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
      $ins->execute([$uid, $date, $slot, $row['fdc_id'], $row['name'], $row['calories'], $row['protein'], $row['carbs'], $row['fat'], $row['fiber'], $row['serving'], $row['image_url'], $row['instructions']]);

      json_ok([
        'date' => $date,
        'slot' => $slot,
        'meal' => $row
      ], 'Meal swapped successfully');
    } else {
      json_error('Failed to regenerate slot alternative.');
    }
  }

} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

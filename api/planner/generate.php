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

  // Target macros for the day (roughly)
  $slots = [
    'breakfast' => ['cal' => (int)($totalCal * 0.25)],
    'lunch'     => ['cal' => (int)($totalCal * 0.33)],
    'dinner'    => ['cal' => (int)($totalCal * 0.33)],
    'snack'     => ['cal' => (int)($totalCal * 0.09)],
  ];

  // Fetch all ETM foods into memory for fast matching
  $foodsQuery = $db->query("SELECT * FROM etm_foods ORDER BY RAND()")->fetchAll();

  if (empty($foodsQuery)) {
      json_error('Your local EatThisMuch database is empty! Please run the scraper first.', 500);
  }

  $insertSt = $db->prepare('
    INSERT INTO meal_plans (user_id, plan_date, meal_slot, fdc_id, etm_food_id, food_name, calories,
      protein_g, carbs_g, fat_g, fiber_g, serving_size, image_url, instructions)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
      fdc_id=VALUES(fdc_id), etm_food_id=VALUES(etm_food_id), food_name=VALUES(food_name),
      calories=VALUES(calories), protein_g=VALUES(protein_g),
      carbs_g=VALUES(carbs_g), fat_g=VALUES(fat_g),
      fiber_g=VALUES(fiber_g), serving_size=VALUES(serving_size),
      image_url=VALUES(image_url), instructions=VALUES(instructions)
  ');

  $plan = [];
  $usedMeals = [];

  foreach ($slots as $slot => $config) {
    // 1. Check lock
    $checkLock = $db->prepare('SELECT * FROM meal_plans WHERE user_id=? AND plan_date=? AND meal_slot=?');
    $checkLock->execute([$uid, $date, $slot]);
    $existing = $checkLock->fetch();
    
    if ($existing && $existing['is_locked']) {
      $plan[$slot] = [
        'fdc_id'   => $existing['fdc_id'],
        'etm_food_id' => $existing['etm_food_id'],
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
      $usedMeals[] = $existing['etm_food_id'] ?? $existing['fdc_id'];
      continue;
    }

    $targetCal = $config['cal'];
    $bestMatch = null;
    $bestDiff = 999999;
    
    // Find a good calorie match in our randomized dataset
    foreach ($foodsQuery as $f) {
        if (in_array($f['id'], $usedMeals)) continue;
        
        $cal = (int)$f['calories'];
        if ($cal <= 0) continue;
        
        $diff = abs($cal - $targetCal);
        
        // Minor penalty to prevent assigning "heavy" dinners to snacks if we don't have to
        if ($slot === 'snack' && $cal > 400) $diff += 500;
        if ($slot === 'breakfast' && $cal > 800) $diff += 300;
        
        // If within 15% tolerance, accept it immediately (since array is randomized)
        if ($diff <= ($targetCal * 0.15)) {
            $bestMatch = $f;
            break;
        }

        if ($diff < $bestDiff) {
            $bestDiff = $diff;
            $bestMatch = $f;
        }
    }

    if ($bestMatch) {
      $usedMeals[] = $bestMatch['id'];
      
      $directions = json_decode($bestMatch['directions'] ?? '[]', true);
      $instText = '';
      if (is_array($directions)) {
          foreach ($directions as $idx => $step) {
              $text = is_array($step) ? ($step['text'] ?? $step['step'] ?? '') : $step;
              $instText .= ($idx + 1) . '. ' . $text . "\n";
          }
      }

      $row = [
        'fdc_id'       => $bestMatch['id'], // Legacy backward compat
        'etm_food_id'  => $bestMatch['id'], // Explicit new link
        'name'         => $bestMatch['name'],
        'calories'     => round($bestMatch['calories']),
        'protein'      => round($bestMatch['protein'], 1),
        'carbs'        => round($bestMatch['total_carbs'], 1),
        'fat'          => round($bestMatch['total_fat'], 1),
        'fiber'        => round($bestMatch['dietary_fiber'], 1),
        'serving'      => $bestMatch['serving_size'] ?: '1 serving',
        'image_url'    => $bestMatch['image_url'],
        'instructions' => trim($instText)
      ];

      // Save to database
      $insertSt->execute([
        $uid, $date, $slot,
        $row['fdc_id'],
        $row['etm_food_id'],
        $row['name'],
        $row['calories'],
        $row['protein'],
        $row['carbs'],
        $row['fat'],
        $row['fiber'],
        $row['serving'],
        $row['image_url'],
        $row['instructions']
      ]);

      $row['is_locked'] = 0;
      $plan[$slot] = $row;
    }
  }

  echo json_encode([
    'status' => 'success',
    'date'   => $date,
    'plan'   => $plan
  ]);

} catch (PDOException $e) {
  json_error('DB Error: ' . $e->getMessage(), 500);
}

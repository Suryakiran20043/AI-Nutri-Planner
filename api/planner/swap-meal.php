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

try {
  if ($action === 'toggle_lock') {
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
    $etmFoodId    = (int) ($body['fdc_id'] ?? 0); // Frontend passes id as fdc_id
    $foodName     = trim($body['food_name'] ?? '');
    $calories     = (int) ($body['calories'] ?? 0);
    $protein      = (float) ($body['protein_g'] ?? 0);
    $carbs        = (float) ($body['carbs_g'] ?? 0);
    $fat          = (float) ($body['fat_g'] ?? 0);
    $fiber        = (float) ($body['fiber_g'] ?? 0);
    $servingSize  = trim($body['serving_size'] ?? '1 serving');
    $imageUrl     = trim($body['image_url'] ?? '');
    $instructions = trim($body['instructions'] ?? '');

    if (!$etmFoodId || !$foodName) {
      json_error('Food details are required.');
    }

    $st = $db->prepare('
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
    $st->execute([$uid, $date, $slot, $etmFoodId, $etmFoodId, $foodName, $calories, $protein, $carbs, $fat, $fiber, $servingSize, $imageUrl ?: null, $instructions ?: null]);

    json_ok([
      'date' => $date,
      'slot' => $slot,
      'meal' => [
        'fdc_id'   => $etmFoodId,
        'etm_food_id' => $etmFoodId,
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
    $st = $db->prepare('SELECT * FROM user_profiles WHERE user_id=?');
    $st->execute([$uid]);
    $profile = $st->fetch();
    if (!$profile || !$profile['daily_calories']) {
      json_error('Profile targets not set. Please complete the calculator first.');
    }

    $totalCal = (int) $profile['daily_calories'];
    $slots = [
      'breakfast' => ['cal' => (int)($totalCal * 0.25)],
      'lunch'     => ['cal' => (int)($totalCal * 0.33)],
      'dinner'    => ['cal' => (int)($totalCal * 0.33)],
      'snack'     => ['cal' => (int)($totalCal * 0.09)],
    ];
    $targetCal = $slots[$slot]['cal'] ?? 500;

    $foodsQuery = $db->query("SELECT * FROM etm_foods ORDER BY RAND() LIMIT 200")->fetchAll();
    if (empty($foodsQuery)) {
        json_error('Your local EatThisMuch database is empty! Please run the scraper first.', 500);
    }

    $bestMatch = null;
    $bestDiff = 999999;
    // Find a good calorie match in our randomized dataset
    foreach ($foodsQuery as $f) {
        $cal = (int)$f['calories'];
        if ($cal <= 0) continue;
        
        $diff = abs($cal - $targetCal);
        
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
      $directions = json_decode($bestMatch['directions'], true);
      $instText = '';
      if (is_array($directions)) {
          foreach ($directions as $idx => $step) {
              $text = is_array($step) ? ($step['text'] ?? $step['step'] ?? '') : $step;
              $instText .= ($idx + 1) . '. ' . $text . "\n";
          }
      }

      $row = [
        'fdc_id'       => $bestMatch['id'], 
        'etm_food_id'  => $bestMatch['id'], 
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

      $ins = $db->prepare('
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
      $ins->execute([$uid, $date, $slot, $row['fdc_id'], $row['etm_food_id'], $row['name'], $row['calories'], $row['protein'], $row['carbs'], $row['fat'], $row['fiber'], $row['serving'], $row['image_url'], $row['instructions']]);

      $row['is_locked'] = 0;
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

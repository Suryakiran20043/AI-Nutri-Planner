<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$age      = (int)   ($body['age']            ?? 25);
$gender   =         ($body['gender']         ?? 'male');
$weight   = (float) ($body['weight_kg']      ?? 70);
$height   = (float) ($body['height_cm']      ?? 170);
$activity =         ($body['activity_level'] ?? 'moderate');
$goal     =         ($body['goal']           ?? 'maintain');
$diet     =         ($body['diet_type']      ?? 'anything');

// BMR — Mifflin-St Jeor
if ($gender === 'male') {
  $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
} else {
  $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
}

$factors = ['sedentary'=>1.2,'light'=>1.375,'moderate'=>1.55,'active'=>1.725,'very_active'=>1.9];
$tdee    = (int) round($bmr * ($factors[$activity] ?? 1.55));

$adjustments = ['lose' => -500, 'maintain' => 0, 'gain' => 300];
$daily_cal   = $tdee + ($adjustments[$goal] ?? 0);

// Cap at a reasonable minimum of 1200 calories
if ($daily_cal < 1200) {
  $daily_cal = 1200;
}

$protein_g = (int) round($daily_cal * 0.30 / 4);
$carbs_g   = (int) round($daily_cal * 0.40 / 4);
$fat_g     = (int) round($daily_cal * 0.30 / 9);

$db = get_db();

try {
  $st = $db->prepare('
    INSERT INTO user_profiles
      (user_id, age, gender, weight_kg, height_cm, activity_level, goal, diet_type,
       bmr, tdee, daily_calories, protein_g, carbs_g, fat_g)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ON DUPLICATE KEY UPDATE
      age=VALUES(age), gender=VALUES(gender), weight_kg=VALUES(weight_kg),
      height_cm=VALUES(height_cm), activity_level=VALUES(activity_level),
      goal=VALUES(goal), diet_type=VALUES(diet_type), bmr=VALUES(bmr),
      tdee=VALUES(tdee), daily_calories=VALUES(daily_calories),
      protein_g=VALUES(protein_g), carbs_g=VALUES(carbs_g), fat_g=VALUES(fat_g)
  ');
  $st->execute([$uid,$age,$gender,$weight,$height,$activity,$goal,$diet,
                (int)$bmr,$tdee,$daily_cal,$protein_g,$carbs_g,$fat_g]);

  json_ok([
    'bmr'            => (int) $bmr,
    'tdee'           => $tdee,
    'daily_calories' => $daily_cal,
    'protein_g'      => $protein_g,
    'carbs_g'        => $carbs_g,
    'fat_g'          => $fat_g,
  ]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

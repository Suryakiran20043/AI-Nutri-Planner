<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$uid  = auth_check();
$date = $_GET['date'] ?? date('Y-m-d');

$db = get_db();
try {
  $st = $db->prepare('SELECT * FROM meal_plans WHERE user_id = ? AND plan_date = ?');
  $st->execute([$uid, $date]);
  $rows = $st->fetchAll();

  $plan = [];
  foreach ($rows as $row) {
    $plan[$row['meal_slot']] = [
      'id'          => $row['id'],
      'fdc_id'      => $row['fdc_id'],
      'etm_food_id' => $row['etm_food_id'],
      'name'        => $row['food_name'],
      'calories'    => (int)$row['calories'],
      'protein'     => (float)$row['protein_g'],
      'carbs'       => (float)$row['carbs_g'],
      'fat'         => (float)$row['fat_g'],
      'fiber'       => (float)$row['fiber_g'],
      'serving'     => $row['serving_size'],
      'image_url'   => $row['image_url'],
      'instructions'=> $row['instructions'],
      'is_locked'   => (int)$row['is_locked']
    ];
  }

  json_ok(['date' => $date, 'plan' => $plan]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

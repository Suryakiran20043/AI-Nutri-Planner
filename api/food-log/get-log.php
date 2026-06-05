<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$uid  = auth_check();
$date = $_GET['date'] ?? date('Y-m-d');

$db = get_db();

try {
  $st = $db->prepare('SELECT * FROM food_log WHERE user_id = ? AND log_date = ? ORDER BY logged_at ASC');
  $st->execute([$uid, $date]);
  $logs = $st->fetchAll();
  
  // Format items nicely
  $items = [];
  $totals = [
    'calories' => 0,
    'protein'  => 0.0,
    'carbs'    => 0.0,
    'fat'      => 0.0
  ];
  
  foreach ($logs as $log) {
    $qty = (float) $log['quantity'];
    $cal = round($log['calories'] * $qty);
    $prot = round($log['protein_g'] * $qty, 1);
    $carb = round($log['carbs_g'] * $qty, 1);
    $ft = round($log['fat_g'] * $qty, 1);
    
    $items[] = [
      'id'         => $log['id'],
      'fdc_id'     => $log['fdc_id'],
      'food_name'  => $log['food_name'],
      'calories'   => $cal,
      'protein_g'  => $prot,
      'carbs_g'    => $carb,
      'fat_g'      => $ft,
      'quantity'   => $qty,
      'unit'       => $log['unit'],
      'logged_at'  => $log['logged_at']
    ];
    
    $totals['calories'] += $cal;
    $totals['protein']  += $prot;
    $totals['carbs']    += $carb;
    $totals['fat']      += $ft;
  }
  
  json_ok(['date' => $date, 'items' => $items, 'totals' => $totals]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$uid        = auth_check();
$week_start = $_GET['week_start'] ?? date('Y-m-d', strtotime('monday this week'));

$db = get_db();

try {
  $st = $db->prepare('SELECT * FROM grocery_items WHERE user_id = ? AND week_start = ? ORDER BY category, food_name');
  $st->execute([$uid, $week_start]);
  $items = $st->fetchAll();

  // Group by category
  $grouped = [
    'produce' => [],
    'protein' => [],
    'dairy'   => [],
    'grains'  => [],
    'pantry'  => [],
    'other'   => []
  ];

  foreach ($items as $item) {
    $cat = $item['category'] ?: 'other';
    $grouped[$cat][] = [
      'id'         => $item['id'],
      'food_name'  => $item['food_name'],
      'quantity'   => $item['quantity'],
      'is_checked' => (int)$item['is_checked']
    ];
  }

  json_ok(['week_start' => $week_start, 'items' => $grouped]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

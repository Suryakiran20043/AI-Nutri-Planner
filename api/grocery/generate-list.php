<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid        = auth_check();
$body       = json_decode(file_get_contents('php://input'), true);
$week_start = $body['week_start'] ?? date('Y-m-d', strtotime('monday this week'));

$db = get_db();

try {
  // Delete old unchecked list items for this week to prevent duplicates
  $db->prepare('DELETE FROM grocery_items WHERE user_id=? AND week_start=? AND is_checked=0')->execute([$uid,$week_start]);

  // Fetch already checked items to avoid re-adding them
  $checkedSt = $db->prepare('SELECT food_name FROM grocery_items WHERE user_id=? AND week_start=? AND is_checked=1');
  $checkedSt->execute([$uid, $week_start]);
  $checkedItems = $checkedSt->fetchAll(PDO::FETCH_COLUMN);

  // Get all meal plans for the week (Monday to Sunday)
  $end = date('Y-m-d', strtotime($week_start . ' +6 days'));
  $st  = $db->prepare('SELECT food_name, serving_size FROM meal_plans WHERE user_id=? AND plan_date BETWEEN ? AND ?');
  $st->execute([$uid,$week_start,$end]);
  $meals = $st->fetchAll();

  // Simple categorize function
  function categorize(string $name): string {
    $name = strtolower($name);
    if (preg_match('/chicken|beef|salmon|fish|egg|tuna|pork|turkey|shrimp|meat|bacon|poultry/', $name)) return 'protein';
    if (preg_match('/milk|yogurt|cheese|butter|cream|dairy/', $name)) return 'dairy';
    if (preg_match('/rice|pasta|bread|oat|quinoa|flour|wheat|cereal|spaghetti|noodle/', $name)) return 'grains';
    if (preg_match('/apple|banana|spinach|broccoli|carrot|tomato|onion|lettuce|berry|fruit|vegetable|salad|potato|avocad|lemon|lime/', $name)) return 'produce';
    return 'pantry';
  }

  $insSt = $db->prepare('INSERT INTO grocery_items (user_id, week_start, food_name, quantity, category) VALUES (?,?,?,?,?)');
  $added = 0;
  
  // Track duplicates in the same generation loop
  $processedNames = [];

  foreach ($meals as $meal) {
    if (empty($meal['food_name'])) continue;
    $name = trim($meal['food_name']);
    
    // Normalize string key to group similar items
    $key = strtolower($name);
    
    if (in_array($key, $processedNames) || in_array($name, $checkedItems)) {
      continue;
    }
    
    $cat = categorize($name);
    $serving = $meal['serving_size'] ?: '1 serving';
    $insSt->execute([$uid, $week_start, $name, $serving, $cat]);
    $processedNames[] = $key;
    $added++;
  }

  json_ok(['items_added' => $added]);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

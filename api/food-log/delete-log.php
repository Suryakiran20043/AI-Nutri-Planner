<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$logId = (int) ($body['id'] ?? 0);
if (!$logId) json_error('Log ID is required');

$db = get_db();

try {
  $st = $db->prepare('DELETE FROM food_log WHERE id = ? AND user_id = ?');
  $st->execute([$logId, $uid]);
  
  if ($st->rowCount() === 0) {
    json_error('Log entry not found or unauthorized', 404);
  }
  
  json_ok([], 'Log entry deleted successfully');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

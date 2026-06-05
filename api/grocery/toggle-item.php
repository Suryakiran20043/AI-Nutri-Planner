<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
$uid  = auth_check();
$body = json_decode(file_get_contents('php://input'), true);

$id      = (int) ($body['id'] ?? 0);
$checked = (int) ($body['checked'] ?? 0);

if (!$id) json_error('Item ID is required');

$db = get_db();

try {
  $st = $db->prepare('UPDATE grocery_items SET is_checked = ? WHERE id = ? AND user_id = ?');
  $st->execute([$checked, $id, $uid]);

  if ($st->rowCount() === 0) {
    json_error('Item not found or unauthorized', 404);
  }

  json_ok(['id' => $id, 'is_checked' => $checked], 'Item toggled');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

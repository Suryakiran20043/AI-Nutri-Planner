<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

$uid = auth_check();
$db = get_db();

try {
  $st = $db->prepare('
    SELECT u.name, u.email, p.* 
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id 
    WHERE u.id = ?
  ');
  $st->execute([$uid]);
  $profile = $st->fetch();

  if (!$profile) {
    json_error('Profile not found', 404);
  }

  json_ok($profile);
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

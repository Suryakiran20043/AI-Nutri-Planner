<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
$email = trim($body['email'] ?? '');
$pass  = $body['password']   ?? '';

if (!$email || !$pass) json_error('Email and password required');

$db = get_db();

try {
  $st = $db->prepare('
    SELECT u.id, u.name, u.password_hash, p.daily_calories 
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id 
    WHERE u.email = ?
  ');
  $st->execute([$email]);
  $user = $st->fetch();

  if (!$user || !password_verify($pass, $user['password_hash'])) {
    json_error('Invalid email or password', 401);
  }

  $_SESSION['user_id'] = $user['id'];
  $_SESSION['name']    = $user['name'];

  $profile_completed = (!empty($user['daily_calories']) && (int)$user['daily_calories'] > 0);

  json_ok([
    'user_id' => $user['id'], 
    'name' => $user['name'],
    'profile_completed' => $profile_completed
  ], 'Login successful');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

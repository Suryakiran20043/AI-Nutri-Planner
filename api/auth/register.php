<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
$name  = trim($body['name']  ?? '');
$email = trim($body['email'] ?? '');
$pass  = $body['password']   ?? '';

if (!$name || !$email || !$pass) json_error('All fields required');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email');
if (strlen($pass) < 6) json_error('Password must be at least 6 characters');

$db = get_db();

try {
  $st = $db->prepare('SELECT id FROM users WHERE email = ?');
  $st->execute([$email]);
  if ($st->fetch()) json_error('Email already registered');

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $ins  = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)');
  $ins->execute([$name, $email, $hash]);
  $userId = (int) $db->lastInsertId();

  // Create profile row for user
  $db->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$userId]);

  $_SESSION['user_id'] = $userId;
  $_SESSION['name']    = $name;

  json_ok(['user_id' => $userId, 'name' => $name], 'Registered successfully');
} catch (PDOException $e) {
  json_error('Database error: ' . $e->getMessage(), 500);
}

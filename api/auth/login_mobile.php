<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
$mobile = trim($body['mobile_number'] ?? '');
$pass  = $body['password'] ?? '';

if (!$mobile || !$pass) json_error('Mobile number and password are required');

$db = get_db();

try {
    $st = $db->prepare('
        SELECT u.id, u.name, u.password_hash, p.daily_calories 
        FROM users u 
        LEFT JOIN user_profiles p ON u.id = p.user_id 
        WHERE u.mobile_number = ?
    ');
    $st->execute([$mobile]);
    $user = $st->fetch();

    // If no password_hash is set, it means they might have registered via OTP only
    if (!$user || empty($user['password_hash']) || !password_verify($pass, $user['password_hash'])) {
        json_error('Invalid mobile number or password', 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'] ?? 'User';

    $profile_completed = (!empty($user['daily_calories']) && (int)$user['daily_calories'] > 0);

    json_ok([
        'user_id' => $user['id'], 
        'name' => $user['name'],
        'profile_completed' => $profile_completed
    ], 'Login successful');

} catch (PDOException $e) {
    json_error('Database error: ' . $e->getMessage(), 500);
}

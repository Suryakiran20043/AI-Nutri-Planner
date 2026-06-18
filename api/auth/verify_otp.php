<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
$mobile = trim($body['mobile_number'] ?? '');
$otp = trim($body['otp'] ?? '');

if (!$mobile || !$otp) json_error('Mobile number and OTP are required');

$db = get_db();

try {
    $st = $db->prepare('
        SELECT u.id, u.name, u.otp_code, u.otp_expires_at, p.daily_calories 
        FROM users u 
        LEFT JOIN user_profiles p ON u.id = p.user_id 
        WHERE u.mobile_number = ?
    ');
    $st->execute([$mobile]);
    $user = $st->fetch();

    if (!$user) {
        json_error('Mobile number not found', 404);
    }

    if ($user['otp_code'] !== $otp) {
        json_error('Invalid OTP', 401);
    }

    if (strtotime($user['otp_expires_at']) < time()) {
        json_error('OTP has expired', 401);
    }

    // Clear the OTP
    $db->prepare('UPDATE users SET otp_code = NULL, otp_expires_at = NULL WHERE id = ?')->execute([$user['id']]);

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

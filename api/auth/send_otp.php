<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
$mobile = trim($body['mobile_number'] ?? '');

if (!$mobile) json_error('Mobile number is required');

$db = get_db();

try {
    // Generate a 6 digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Check if user exists with this mobile
    $st = $db->prepare('SELECT id FROM users WHERE mobile_number = ?');
    $st->execute([$mobile]);
    $user = $st->fetch();

    if ($user) {
        $update = $db->prepare('UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?');
        $update->execute([$otp, $expires_at, $user['id']]);
    } else {
        // Mock user creation for OTP login, they might need to complete profile later
        $ins = $db->prepare('INSERT INTO users (mobile_number, otp_code, otp_expires_at) VALUES (?, ?, ?)');
        $ins->execute([$mobile, $otp, $expires_at]);
        $userId = (int) $db->lastInsertId();
        
        $db->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$userId]);
    }

    // In a real application, send the OTP via SMS API (Twilio, AWS SNS, etc.)
    // For this prototype, we return it in the response for local testing
    json_ok(['simulated_otp' => $otp, 'expires_at' => $expires_at], 'OTP sent successfully (Simulated)');

} catch (PDOException $e) {
    json_error('Database error: ' . $e->getMessage(), 500);
}

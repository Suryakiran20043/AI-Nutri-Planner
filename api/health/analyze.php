<?php
error_reporting(0);
set_time_limit(120); // Allow up to 120 seconds for heavy OCR and LLM processing
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

// Enforce auth checking
$uid = auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

if (empty($_FILES['report'])) {
    json_error('No health report file was uploaded.', 400);
}

$file = $_FILES['report'];
$fileName = basename($file['name']);
$fileSize = $file['size'];
$tmpPath = $file['tmp_name'];
$error = $file['error'];

if ($error !== UPLOAD_ERR_OK) {
    json_error('Upload failed with error code: ' . $error, 400);
}

// 1. Create secure uploads directory on the PHP side
$targetDir = dirname(__DIR__, 2) . '/uploads/health_reports/';
if (!is_dir($targetDir)) {
    if (!@mkdir($targetDir, 0755, true)) {
        json_error('Failed to create secure storage directory.', 500);
    }
}

// Generate secure file path
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExts = ['pdf', 'png', 'jpg', 'jpeg', 'tiff', 'bmp'];
if (!in_array($fileExt, $allowedExts)) {
    json_error('Unsupported file format. Please upload a PDF or image.', 400);
}

$secureFileName = $uid . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
$secureFilePath = $targetDir . $secureFileName;

// Move uploaded file to secure location
if (!@move_uploaded_file($tmpPath, $secureFilePath)) {
    json_error('Failed to store the uploaded file. Check directory permissions.', 500);
}

$db = get_db();
$targetCalories = 2000; // Default fallback
$allergies = '';
$dietType = 'anything';
$age = 30;
$gender = 'male';
$weightKg = 70.0;
$heightCm = 170.0;

try {
    $st = $db->prepare('SELECT daily_calories, allergies, diet_type, age, gender, weight_kg, height_cm FROM user_profiles WHERE user_id=?');
    $st->execute([$uid]);
    $profile = $st->fetch();
    if ($profile) {
        if (!empty($profile['daily_calories'])) $targetCalories = (int) $profile['daily_calories'];
        $allergies = $profile['allergies'] ?? '';
        $dietType = $profile['diet_type'] ?? 'anything';
        if (!empty($profile['age'])) $age = (int) $profile['age'];
        if (!empty($profile['gender'])) $gender = $profile['gender'];
        if (!empty($profile['weight_kg'])) $weightKg = (float) $profile['weight_kg'];
        if (!empty($profile['height_cm'])) $heightCm = (float) $profile['height_cm'];
    }
} catch (PDOException $e) {
    // Non-fatal, fallback to defaults
}

// 2b. Fetch user favorites
$favorites = [];
try {
    $favSt = $db->prepare('SELECT food_name FROM favorites WHERE user_id=?');
    $favSt->execute([$uid]);
    while ($row = $favSt->fetch()) {
        $favorites[] = $row['food_name'];
    }
} catch (PDOException $e) {
    // Non-fatal
}
$favoritesStr = implode(', ', $favorites);

// 3. Forward the file to the FastAPI microservice via native PHP streams
$boundary = bin2hex(random_bytes(16));
$payload = "";

// File part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="file"; filename="' . $fileName . "\"\r\n";
$payload .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
$payload .= file_get_contents($secureFilePath) . "\r\n";

// target_calories part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="target_calories"' . "\r\n\r\n";
$payload .= $targetCalories . "\r\n";

// user_id part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="user_id"' . "\r\n\r\n";
$payload .= $uid . "\r\n";

// allergies part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="allergies"' . "\r\n\r\n";
$payload .= $allergies . "\r\n";

// diet_type part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="diet_type"' . "\r\n\r\n";
$payload .= $dietType . "\r\n";

// favorites part
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="favorites"' . "\r\n\r\n";
$payload .= $favoritesStr . "\r\n";

// age
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="age"' . "\r\n\r\n";
$payload .= $age . "\r\n";

// gender
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="gender"' . "\r\n\r\n";
$payload .= $gender . "\r\n";

// weight
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="weight_kg"' . "\r\n\r\n";
$payload .= $weightKg . "\r\n";

// height
$payload .= "--" . $boundary . "\r\n";
$payload .= 'Content-Disposition: form-data; name="height_cm"' . "\r\n\r\n";
$payload .= $heightCm . "\r\n";

$payload .= "--" . $boundary . "--\r\n";

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => [
            'X-API-KEY: nutriplan_secure_communication_key_2026',
            'Accept: application/json',
            'Content-Type: multipart/form-data; boundary=' . $boundary
        ],
        'content' => $payload,
        'timeout' => 90,
        'ignore_errors' => true
    ]
];
$context = stream_context_create($opts);
$response = @file_get_contents('http://127.0.0.1:8005/api/v1/analyze-report', false, $context);

$httpCode = 0;
if (isset($http_response_header) && is_array($http_response_header)) {
    if (preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $match)) {
        $httpCode = (int)$match[1];
    }
}
$curlError = ($response === false) ? 'Failed to connect to FastAPI service' : '';

// If FastAPI service is not running or failed
if ($httpCode !== 200) {
    // Clean up file if we failed
    if (file_exists($secureFilePath)) {
        unlink($secureFilePath);
    }
    $respData = $response ? json_decode($response, true) : null;
    $detail = $curlError ?: ($respData['detail'] ?? 'FastAPI Service Error: ' . substr($response, 0, 100));
    if (is_array($detail)) {
        $detail = json_encode($detail);
    }
    json_error('Health analysis service offline or failed: ' . $detail, 500);
}

$data = json_decode($response, true);
if ($data === null || empty($data['status']) || $data['status'] !== 'success') {
    if (file_exists($secureFilePath)) {
        unlink($secureFilePath);
    }
    json_error('Invalid response payload from analysis service.', 500);
}

// 4. Save results to SQL database tables
try {
    $db->beginTransaction();

    // Insert Health Report log
    $reportSt = $db->prepare('
        INSERT INTO user_health_reports (user_id, file_name, file_path, status, raw_text, overall_risk_score)
        VALUES (?, ?, ?, "PROCESSED", ?, ?)
    ');
    $reportSt->execute([
        $uid,
        $fileName,
        'uploads/health_reports/' . $secureFileName,
        $data['raw_text'],
        (int)($data['overall_risk_score'] ?? 0)
    ]);
    $reportId = $db->lastInsertId();

    // Insert parsed biomarkers
    $biomarkerSt = $db->prepare('
        INSERT INTO user_biomarkers (report_id, user_id, biomarker_name, measured_value, unit, reference_range, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    foreach ($data['biomarkers'] as $key => $bio) {
        $biomarkerSt->execute([
            $reportId,
            $uid,
            $key,
            $bio['value'],
            $bio['unit'],
            $bio['reference_range'],
            $bio['status']
        ]);
    }

    // Insert predicted health risks
    $riskSt = $db->prepare('
        INSERT INTO user_health_risks (report_id, user_id, risk_condition, severity, dietary_rules, risk_percentage)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    foreach ($data['health_risks'] as $risk) {
        $riskSt->execute([
            $reportId,
            $uid,
            $risk['condition'],
            $risk['severity'],
            json_encode($data['meal_plan']['dietary_summary']),
            (int)($risk['risk_pct'] ?? 0)
        ]);
    }

    // Save and overwrite the generated medical meal plan in `meal_plans` for today
    $planDate = date('Y-m-d');
    $mealSt = $db->prepare('
        INSERT INTO meal_plans (user_id, plan_date, meal_slot, fdc_id, food_name, calories, protein_g, carbs_g, fat_g, fiber_g, serving_size, image_url, instructions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            fdc_id=VALUES(fdc_id),
            food_name=VALUES(food_name),
            calories=VALUES(calories),
            protein_g=VALUES(protein_g),
            carbs_g=VALUES(carbs_g),
            fat_g=VALUES(fat_g),
            fiber_g=VALUES(fiber_g),
            serving_size=VALUES(serving_size),
            image_url=VALUES(image_url),
            instructions=VALUES(instructions),
            is_locked=0
    ');

    $meals = $data['meal_plan']['meals'];
    foreach ($meals as $slot => $meal) {
        $mealSt->execute([
            $uid,
            $planDate,
            $slot,
            rand(1000000, 9999999), // Mock unique ID for references
            $meal['name'],
            $meal['calories'],
            $meal['protein'],
            $meal['carbs'],
            $meal['fat'],
            $meal['fiber'] ?? 0.0,
            '1 serving',
            $meal['image_url'] ?? null,
            $meal['instructions']
        ]);
    }

    $db->commit();
    
    // Return the response data directly so UI can show reports dashboard
    json_ok([
        'report_id' => $reportId,
        'biomarkers' => $data['biomarkers'],
        'health_risks' => $data['health_risks'],
        'meal_plan' => $data['meal_plan']
    ], 'Report analyzed and custom medical meal plan compiled successfully!');

} catch (Throwable $e) {
    $db->rollBack();
    if (file_exists($secureFilePath)) {
        unlink($secureFilePath);
    }
    json_error('Failed to log report analysis: ' . $e->getMessage(), 500);
}

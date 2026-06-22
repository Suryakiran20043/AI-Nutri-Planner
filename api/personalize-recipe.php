<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    // If not logged in, just use a dummy user_id for the AI to work
    $_SESSION['user_id'] = 1;
}

$input = json_decode(file_get_contents('php://input'), true);
$recipe_id = $input['recipe_id'] ?? 'unknown';
$recipe_name = $input['recipe_name'] ?? 'Personalized Meal';

$payload = json_encode([
    'recipe_id' => (string)$recipe_id,
    'recipe_name' => (string)$recipe_name,
    'base_calories' => 400,
    'user_id' => $_SESSION['user_id']
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => $payload,
        'ignore_errors' => true // To prevent PHP from throwing a warning on 4xx/5xx HTTP status
    ]
];
$context  = stream_context_create($options);

$response = @file_get_contents('http://127.0.0.1:8005/api/v1/personalize-recipe', false, $context);

if ($response === FALSE) {
    echo json_encode(['status' => 'error', 'message' => 'AI Service unreachable. Please ensure the Python server is running on port 8005.']);
} else {
    echo $response;
}
?>

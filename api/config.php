<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutriplan');
define('DB_USER', 'root');
define('DB_PASS', '');

// Get your FREE key from: https://api.data.gov/signup
// DEMO_KEY works for testing (30 req/hr, 50 req/day)
define('USDA_API_KEY', 'DEMO_KEY');
define('USDA_BASE_URL', 'https://api.nal.usda.gov/fdc/v1');

// Get your Spoonacular API Key from: https://spoonacular.com/food-api/console/info
// Free plan provides 150 points/day
define('SPOONACULAR_API_KEY', 'YOUR_SPOONACULAR_API_KEY'); 
define('SPOONACULAR_BASE_URL', 'https://api.spoonacular.com');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

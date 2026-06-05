<?php
function json_ok($data = [], string $message = 'success'): void {
  echo json_encode(['status' => 'ok', 'message' => $message, 'data' => $data]);
  exit;
}

function json_error(string $message, int $code = 400): void {
  http_response_code($code);
  echo json_encode(['status' => 'error', 'message' => $message]);
  exit;
}

function auth_check(): int {
  if (empty($_SESSION['user_id'])) {
    json_error('Not authenticated', 401);
  }
  return (int) $_SESSION['user_id'];
}

function usda_fetch(string $endpoint): array {
  try {
    if (USDA_API_KEY === 'DEMO_KEY' || empty(USDA_API_KEY)) {
      throw new Exception('Using demo key - auto fallback to local food datasets');
    }
    
    $url = USDA_BASE_URL . $endpoint;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 8,
      CURLOPT_HTTPHEADER     => [
        'X-Api-Key: ' . USDA_API_KEY,
        'Accept: application/json'
      ],
      CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($code !== 200 || !$body) {
      throw new Exception('USDA API error code ' . $code);
    }
    
    $data = json_decode($body, true);
    if ($data === null) {
      throw new Exception('Invalid JSON received from USDA');
    }
    return $data;
  } catch (Exception $e) {
    // Intercept and load from local structured dataset
    require_once __DIR__ . '/local_food_db.php';
    
    $parts = parse_url($endpoint);
    $path  = $parts['path'] ?? '';
    parse_str($parts['query'] ?? '', $queryArgs);
    
    if (strpos($path, '/foods/search') !== false) {
      $query = $queryArgs['query'] ?? '';
      $pageSize = (int) ($queryArgs['pageSize'] ?? 15);
      return LocalFoodDB::getFoods($query, $pageSize);
    } else if (strpos($path, '/food/') !== false) {
      $pathParts = explode('/', trim($path, '/'));
      $fdcId = (int) end($pathParts);
      try {
        return LocalFoodDB::getFoodById($fdcId);
      } catch (Exception $ex) {
        $res = LocalFoodDB::getFoods('', 1);
        return $res['foods'][0];
      }
    } else {
      $query = $queryArgs['query'] ?? '';
      return LocalFoodDB::getFoods($query);
    }
  }
}

function spoonacular_fetch(string $endpoint): array {
  try {
    if (SPOONACULAR_API_KEY === 'YOUR_SPOONACULAR_API_KEY' || empty(SPOONACULAR_API_KEY)) {
      throw new Exception('Spoonacular API key is unconfigured');
    }

    $separator = (strpos($endpoint, '?') === false) ? '?' : '&';
    $url = SPOONACULAR_BASE_URL . $endpoint . $separator . 'apiKey=' . urlencode(SPOONACULAR_API_KEY);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 8,
      CURLOPT_HTTPHEADER     => ['Accept: application/json'],
      CURLOPT_SSL_VERIFYPEER => false
    ]);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($code !== 200 || !$body) {
      throw new Exception('Spoonacular API error ' . $code);
    }

    $data = json_decode($body, true);
    if ($data === null) {
      throw new Exception('Invalid JSON from Spoonacular');
    }

    return $data;
  } catch (Exception $e) {
    // Intercept and load from local structured recipe datasets
    require_once __DIR__ . '/local_food_db.php';
    
    $parts = parse_url($endpoint);
    $path  = $parts['path'] ?? '';
    parse_str($parts['query'] ?? '', $queryArgs);
    
    if (strpos($path, '/recipes/complexSearch') !== false) {
      $query = $queryArgs['query'] ?? '';
      $maxCal = (int) ($queryArgs['maxCalories'] ?? 0);
      $diet = $queryArgs['diet'] ?? 'anything';
      return LocalFoodDB::getRecipes($query, $maxCal, $diet);
    } else if (strpos($path, '/recipes/') !== false) {
      $pathParts = explode('/', trim($path, '/'));
      $recipeId = 0;
      foreach ($pathParts as $part) {
        if (is_numeric($part)) {
          $recipeId = (int) $part;
          break;
        }
      }
      try {
        return LocalFoodDB::getRecipeById($recipeId);
      } catch (Exception $ex) {
        $res = LocalFoodDB::getRecipes('', 0, 'anything');
        return $res['results'][0];
      }
    } else {
      $query = $queryArgs['query'] ?? '';
      return LocalFoodDB::getRecipes($query);
    }
  }
}

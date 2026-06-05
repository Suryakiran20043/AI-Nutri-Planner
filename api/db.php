<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    try {
      $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
      );
    } catch (PDOException $e) {
      http_response_code(500);
      die(json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . $e->getMessage()]));
    }
  }
  return $pdo;
}

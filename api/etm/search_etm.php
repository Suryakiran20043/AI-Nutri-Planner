<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

// Sanitize inputs
$q     = trim($_GET['q'] ?? '');
$sort  = $_GET['sort'] ?? 'name';
$order = strtolower($_GET['order'] ?? 'asc');
$page  = max(1, (int) ($_GET['page'] ?? 1));
$limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));

// Whitelist sort columns to prevent SQL injection
$allowedSorts = ['name', 'calories', 'protein', 'total_carbs', 'total_fat'];
if (!in_array($sort, $allowedSorts, true)) {
  $sort = 'name';
}

// Whitelist order direction
$order = ($order === 'desc') ? 'DESC' : 'ASC';

$offset = ($page - 1) * $limit;
$pdo    = get_db();

// Build WHERE clause
$where  = '';
$params = [];
if ($q !== '') {
  $where = 'WHERE name LIKE :q';
  $params[':q'] = '%' . $q . '%';
}

// Get total count
$countSql = "SELECT COUNT(*) FROM etm_foods {$where}";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

// Fetch paginated results (sort/order are whitelisted, safe to interpolate)
$sql = "SELECT * FROM etm_foods {$where} ORDER BY {$sort} {$order} LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
  $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$foods = $stmt->fetchAll();

// Parse JSON directions for each food
foreach ($foods as &$food) {
  $food['directions'] = json_decode($food['directions'], true) ?? [];
}
unset($food);

json_ok([
  'foods'      => $foods,
  'total'      => $total,
  'page'       => $page,
  'totalPages' => (int) ceil($total / $limit),
]);

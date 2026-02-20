<?php
/**
 * get_cows.php
 * Returns a JSON array of cow listings.
 * Supports optional GET query parameters for filtering:
 *   breed      – partial match (case-insensitive)
 *   location   – partial match (case-insensitive)
 *   min_price  – minimum price (numeric)
 *   max_price  – maximum price (numeric)
 *   search     – searches seller_name, breed, and location
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $pdo = getPDO();

    $conditions = [];
    $params     = [];

    /* Search across multiple columns */
    if (!empty($_GET['search'])) {
        $like = '%' . $_GET['search'] . '%';
        $conditions[] = '(breed LIKE :search1 OR location LIKE :search2 OR seller_name LIKE :search3)';
        $params[':search1'] = $like;
        $params[':search2'] = $like;
        $params[':search3'] = $like;
    }

    /* Breed filter */
    if (!empty($_GET['breed'])) {
        $conditions[] = 'breed LIKE :breed';
        $params[':breed'] = '%' . $_GET['breed'] . '%';
    }

    /* Location filter */
    if (!empty($_GET['location'])) {
        $conditions[] = 'location LIKE :location';
        $params[':location'] = '%' . $_GET['location'] . '%';
    }

    /* Price range */
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $conditions[] = 'price >= :min_price';
        $params[':min_price'] = (float)$_GET['min_price'];
    }
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $conditions[] = 'price <= :max_price';
        $params[':max_price'] = (float)$_GET['max_price'];
    }

    $sql = 'SELECT * FROM cows';
    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cows = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $cows]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

<?php
/**
 * config.php
 * Database connection configuration.
 * Include this file in any PHP script that needs DB access.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'cattle_marketplace');
define('DB_USER', 'root');   // Change if your WAMP MySQL user is different
define('DB_PASS', '');       // Change if you have a MySQL password set

define('UPLOAD_DIR',  __DIR__ . '/uploads/');
define('UPLOAD_URL',  'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTS',  ['jpg', 'jpeg', 'png', 'gif', 'webp']);

/**
 * Returns a PDO connection. Throws PDOException on failure.
 */
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

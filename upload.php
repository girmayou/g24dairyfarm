<?php
/**
 * upload.php
 * Handles new cow listing submissions.
 * Expects a multipart/form-data POST with the following fields:
 *   seller_name, phone, location, breed, age, weight, price, cow_image (file)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

/* ── Helper: send JSON response and exit ── */
function respond(bool $success, string $message = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

/* ── Only accept POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Only POST requests are accepted.');
}

/* ── Sanitise text helper ── */
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

/* ── Collect & validate text fields ── */
$errors = [];

$seller_name = clean($_POST['seller_name'] ?? '');
$phone       = clean($_POST['phone']       ?? '');
$location    = clean($_POST['location']    ?? '');
$breed       = clean($_POST['breed']       ?? '');
$age         = trim($_POST['age']          ?? '');
$weight      = trim($_POST['weight']       ?? '');
$price       = trim($_POST['price']        ?? '');

if ($seller_name === '') $errors[] = 'Seller name is required.';
if ($phone === '')       $errors[] = 'Phone number is required.';
if ($location === '')    $errors[] = 'Location is required.';
if ($breed === '')       $errors[] = 'Breed is required.';

if (!is_numeric($age) || (float)$age <= 0)    $errors[] = 'Age must be a positive number.';
if (!is_numeric($weight) || (float)$weight <= 0) $errors[] = 'Weight must be a positive number.';
if (!is_numeric($price) || (float)$price <= 0)  $errors[] = 'Price must be a positive number.';

/* ── Validate uploaded image ── */
if (!isset($_FILES['cow_image']) || $_FILES['cow_image']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'A cow image is required.';
} elseif ($_FILES['cow_image']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'File upload error (code ' . $_FILES['cow_image']['error'] . ').';
} else {
    $file     = $_FILES['cow_image'];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mimeType, ALLOWED_TYPES, true) || !in_array($ext, ALLOWED_EXTS, true)) {
        $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
    } elseif ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'Image size must not exceed 5 MB.';
    }
}

if (!empty($errors)) {
    respond(false, implode(' ', $errors));
}

/* ── Ensure uploads directory exists ── */
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/* ── Save image with a unique name ── */
$newFilename = uniqid('cow_', true) . '.' . $ext;
$destPath    = UPLOAD_DIR . $newFilename;

if (!move_uploaded_file($_FILES['cow_image']['tmp_name'], $destPath)) {
    respond(false, 'Failed to save image. Check server permissions.');
}

/* ── Insert into database ── */
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO cows (seller_name, phone, location, breed, age, weight, price, image_path)
         VALUES (:seller_name, :phone, :location, :breed, :age, :weight, :price, :image_path)'
    );
    $stmt->execute([
        ':seller_name' => $seller_name,
        ':phone'       => $phone,
        ':location'    => $location,
        ':breed'       => $breed,
        ':age'         => (float)$age,
        ':weight'      => (float)$weight,
        ':price'       => (float)$price,
        ':image_path'  => UPLOAD_URL . $newFilename,
    ]);

    respond(true, 'Listing published successfully!', ['id' => (int)$pdo->lastInsertId()]);

} catch (PDOException $e) {
    // Remove uploaded image if DB insert failed
    if (file_exists($destPath)) unlink($destPath);
    respond(false, 'Database error: ' . $e->getMessage());
}

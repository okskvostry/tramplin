<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';

$userId = $_POST['user_id'] ?? 0;
$photoIndex = $_POST['photo_index'] ?? 1;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Не указан пользователь']);
    exit;
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
    exit;
}

$file = $_FILES['photo'];
$uploadDir = __DIR__ . '/../uploads/offices/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'employer_' . $userId . '_office_' . $photoIndex . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Сохраняем путь в БД
    $field = 'office_photo_' . $photoIndex;
    $stmt = $pdo->prepare("UPDATE employers SET $field = ? WHERE user_id = ?");
    $stmt->execute([$filename, $userId]);
    
    echo json_encode([
        'success' => true, 
        'path' => $filename, 
        'url' => 'uploads/offices/' . $filename,
        'index' => $photoIndex
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла']);
}
?>
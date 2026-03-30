<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? 0;
$photoIndex = $data['photo_index'] ?? 0;

if (!$userId || !$photoIndex) {
    echo json_encode(['success' => false, 'message' => 'Не указаны параметры']);
    exit;
}

$field = 'office_photo_' . $photoIndex;

try {
    // Получаем имя файла
    $stmt = $pdo->prepare("SELECT $field FROM employers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $employer = $stmt->fetch();
    
    if ($employer && $employer[$field]) {
        $filepath = __DIR__ . '/../uploads/offices/' . $employer[$field];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Очищаем поле в БД
        $stmt = $pdo->prepare("UPDATE employers SET $field = NULL WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Фото удалено']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
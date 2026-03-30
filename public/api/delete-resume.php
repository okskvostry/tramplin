<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? 0;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Не указан пользователь']);
    exit;
}

try {
    // Получаем имя файла
    $stmt = $pdo->prepare("SELECT resume_path FROM applicants WHERE user_id = ?");
    $stmt->execute([$userId]);
    $applicant = $stmt->fetch();
    
    if ($applicant && $applicant['resume_path']) {
        $filepath = __DIR__ . '/../uploads/resumes/' . $applicant['resume_path'];
        if (file_exists($filepath)) {
            unlink($filepath); // Удаляем файл
        }
        
        // Очищаем путь в БД
        $stmt = $pdo->prepare("UPDATE applicants SET resume_path = NULL WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Резюме удалено']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
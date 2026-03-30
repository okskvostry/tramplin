<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Нет данных']);
    exit;
}

$employerId = $data['employer_id'] ?? 0;
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$type = $data['type'] ?? '';
$format = $data['format'] ?? '';
$city = $data['city'] ?? '';
$startDate = $data['start_date'] ?? null;
$eventTime = $data['event_time'] ?? null;
$price = $data['price'] ?? '';
$salaryMin = $data['salary_min'] ?? null;
$skills = $data['skills'] ?? [];

if (!$employerId || !$title || !$description || !$type || !$format) {
    echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO opportunities (employer_id, title, description, type, format, city, start_date, time, price, salary_min, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $employerId, $title, $description, $type, $format, $city, $startDate, $eventTime, $price, $salaryMin
    ]);
    
    $opportunityId = $pdo->lastInsertId();
    
    // Сохраняем навыки
    foreach ($skills as $skill) {
        // Добавляем навык, если его нет
        $stmt = $pdo->prepare("INSERT IGNORE INTO skills (name) VALUES (?)");
        $stmt->execute([$skill]);
        
        $stmt = $pdo->prepare("
            INSERT INTO opportunity_skills (opportunity_id, skill_id) 
            SELECT ?, id FROM skills WHERE name = ?
        ");
        $stmt->execute([$opportunityId, $skill]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Возможность создана!', 'id' => $opportunityId]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
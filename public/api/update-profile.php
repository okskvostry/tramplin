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

$userId = $data['user_id'] ?? 0;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Не указан пользователь']);
    exit;
}

// Получаем роль пользователя
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit;
}

try {
    // ========== ДЛЯ СОИСКАТЕЛЯ ==========
    if ($user['role'] === 'applicant') {
        $stmt = $pdo->prepare("
            UPDATE applicants SET 
                last_name = ?,
                first_name = ?,
                patronymic = ?,
                university = ?,
                graduation_year = ?,
                studying_now = ?,
                city = ?,
                birth_date = ?,
                portfolio = ?
            WHERE user_id = ?
        ");
        
        $stmt->execute([
            $data['lastName'] ?? '',
            $data['firstName'] ?? '',
            $data['patronymic'] ?? '',
            $data['university'] ?? '',
            $data['graduationYear'] ?? null,
            isset($data['studyingNow']) ? 1 : 0,
            $data['city'] ?? '',
            $data['birthDate'] ?? null,
            $data['portfolio'] ?? '',
            $userId
        ]);
        
        // Сохраняем навыки
        if (isset($data['skills'])) {
            $stmt = $pdo->prepare("SELECT id FROM applicants WHERE user_id = ?");
            $stmt->execute([$userId]);
            $applicant = $stmt->fetch();
            
            if ($applicant) {
                $applicantId = $applicant['id'];
                
                // Удаляем старые навыки
                $stmt = $pdo->prepare("DELETE FROM applicant_skills WHERE applicant_id = ?");
                $stmt->execute([$applicantId]);
                
                // Добавляем новые навыки
                foreach ($data['skills'] as $skill) {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO skills (name) VALUES (?)");
                    $stmt->execute([$skill]);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO applicant_skills (applicant_id, skill_id) 
                        SELECT ?, id FROM skills WHERE name = ?
                    ");
                    $stmt->execute([$applicantId, $skill]);
                }
            }
        }
    }
    
    // ========== ДЛЯ РАБОТОДАТЕЛЯ ==========
    if ($user['role'] === 'employer') {
        // Проверяем, есть ли запись в employers
        $stmt = $pdo->prepare("SELECT id FROM employers WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Обновляем существующую запись
            $stmt = $pdo->prepare("
                UPDATE employers SET 
                    company_name = ?,
                    description = ?,
                    city = ?,
                    phone = ?,
                    address = ?,
                    industry = ?,
                    website = ?
                WHERE user_id = ?
            ");
            $stmt->execute([
                $data['companyName'] ?? '',
                $data['description'] ?? '',
                $data['city'] ?? '',
                $data['phone'] ?? '',
                $data['address'] ?? '',
                $data['industry'] ?? '',
                $data['website'] ?? '',
                $userId
            ]);
        } else {
            // Создаем новую запись
            $stmt = $pdo->prepare("
                INSERT INTO employers (user_id, company_name, description, city, phone, address, industry, website) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $data['companyName'] ?? '',
                $data['description'] ?? '',
                $data['city'] ?? '',
                $data['phone'] ?? '',
                $data['address'] ?? '',
                $data['industry'] ?? '',
                $data['website'] ?? ''
            ]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Данные обновлены']);
    
} catch (Exception $e) {
    error_log("Ошибка update-profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
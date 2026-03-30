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

$role = $data['role'];
$email = $data['email'];
$name = $data['name'];
$password = $data['password'];

// Проверки
if (empty($email) || empty($name) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email уже зарегистрирован']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Сохраняем пользователя
    $stmt = $pdo->prepare("
        INSERT INTO users (email, name, password_hash, role, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$email, $name, $hashedPassword, $role]);
    $userId = $pdo->lastInsertId();

    if ($role === 'applicant') {
        $stmt = $pdo->prepare("
            INSERT INTO applicants (user_id, last_name, first_name, patronymic, university, graduation_year, studying_now) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $data['lastName'] ?? '',
            $data['firstName'] ?? '',
            $data['patronymic'] ?? '',
            $data['university'] ?? '',
            $data['graduationYear'] ?? null,
            isset($data['studyingNow']) ? 1 : 0
        ]);

        if (!empty($data['skills'])) {
            $applicantId = $pdo->lastInsertId();
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

    if ($role === 'employer') {
        $verificationType = $data['verificationType'] ?? 'inn';
        if ($verificationType === 'inn') {
            $stmt = $pdo->prepare("
                INSERT INTO employers (user_id, inn, website, verification_status) 
                VALUES (?, ?, ?, 'pending')
            ");
            $stmt->execute([$userId, $data['inn'] ?? '', $data['website'] ?? '']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO employers (user_id, egrul_file, verification_status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$userId, $data['fileName'] ?? '']);
        }
    }
    
    if ($role === 'curator') {
        $stmt = $pdo->prepare("
            INSERT INTO curators (user_id) VALUES (?)
        ");
        $stmt->execute([$userId]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'userId' => $userId,
        'role' => $role,
        'name' => $name,
        'message' => 'Регистрация успешна'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}
?>
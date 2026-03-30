<?php
// api/get-profile.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';

$userId = $_GET['user_id'] ?? 0;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Не указан пользователь']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit;
}

$result = [
    'id' => $user['id'],
    'email' => $user['email'],
    'name' => $user['name'],
    'role' => $user['role'],
    'created_at' => $user['created_at']
];

// ========== ДЛЯ СОИСКАТЕЛЯ ==========
if ($user['role'] === 'applicant') {
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE user_id = ?");
    $stmt->execute([$userId]);
    $applicant = $stmt->fetch();
    
    if ($applicant) {
        $result = array_merge($result, $applicant);
        
        // Получаем навыки
        $stmt = $pdo->prepare("
            SELECT s.name FROM skills s
            JOIN applicant_skills aps ON aps.skill_id = s.id
            WHERE aps.applicant_id = ?
        ");
        $stmt->execute([$applicant['id']]);
        $result['skills'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// ========== ДЛЯ РАБОТОДАТЕЛЯ ==========
if ($user['role'] === 'employer') {
    $stmt = $pdo->prepare("SELECT * FROM employers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $employer = $stmt->fetch();
    
    if ($employer) {
        $result = array_merge($result, $employer);
    }
}

echo json_encode(['success' => true, 'data' => $result]);
?>
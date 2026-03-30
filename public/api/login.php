<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role = $data['role'] ?? ''; // добавляем роль

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// ========== ДЛЯ КУРАТОРОВ ==========
if ($role === 'curator') {
    $stmt = $pdo->prepare("SELECT * FROM curators WHERE email = ?");
    $stmt->execute([$email]);
    $curator = $stmt->fetch();
    
    if (!$curator || !password_verify($password, $curator['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Неверный email или пароль куратора']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'userId' => $curator['user_id'],
        'curatorId' => $curator['id'],
        'name' => $curator['name'],
        'email' => $curator['email'],
        'role' => 'curator',
        'institution' => $curator['institution'],
        'message' => 'Вход выполнен'
    ]);
    exit;
}

// ========== ДЛЯ ОБЫЧНЫХ ПОЛЬЗОВАТЕЛЕЙ ==========
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Неверный email или пароль']);
    exit;
}

echo json_encode([
    'success' => true,
    'userId' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'message' => 'Вход выполнен'
]);
?>
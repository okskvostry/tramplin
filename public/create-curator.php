<?php
require_once __DIR__ . '/includes/config.php';

$email = 'curator@tramplin.ru';
$name = 'Оксана';
$password = 'curator123';
$institution = 'АлтГУ';

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo "Хеш для пароля '$password':<br>";
echo "<code>" . htmlspecialchars($hashedPassword) . "</code><br><br>";

try {
    // Проверяем, есть ли уже
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "⚠️ Пользователь с email $email уже существует.<br>";
        echo "Удалите его через phpMyAdmin или выполните:<br>";
        echo "<code>DELETE FROM curators WHERE email = '$email';</code><br>";
        echo "<code>DELETE FROM users WHERE email = '$email';</code><br>";
    } else {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO users (email, name, password_hash, role, created_at) 
            VALUES (?, ?, ?, 'curator', NOW())
        ");
        $stmt->execute([$email, $name, $hashedPassword]);
        $userId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("
            INSERT INTO curators (user_id, email, password_hash, name, institution) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $email, $hashedPassword, $name, $institution]);
        
        $pdo->commit();
        
        echo "✅ Куратор успешно создан!<br>";
        echo "Email: $email<br>";
        echo "Пароль: $password<br>";
        echo "Хеш: " . htmlspecialchars($hashedPassword) . "<br>";
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Ошибка: " . $e->getMessage();
}
?>
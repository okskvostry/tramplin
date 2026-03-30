<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/config.php';

$employerId = $_GET['employer_id'] ?? 0;

if (!$employerId) {
    echo json_encode(['success' => false, 'message' => 'Не указан работодатель']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, u.name as company_name 
    FROM opportunities o
    JOIN users u ON o.employer_id = u.id
    WHERE o.employer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$employerId]);
$opportunities = $stmt->fetchAll();

foreach ($opportunities as &$opp) {
    // Получаем навыки
    $stmt = $pdo->prepare("
        SELECT s.name FROM skills s
        JOIN opportunity_skills os ON os.skill_id = s.id
        WHERE os.opportunity_id = ?
    ");
    $stmt->execute([$opp['id']]);
    $opp['skills'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

echo json_encode(['success' => true, 'data' => $opportunities]);
?>
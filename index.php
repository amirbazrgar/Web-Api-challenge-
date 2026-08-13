<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function decodeQuestion($text) {
    $decoded = base64_decode($text);
    if ($decoded) {
        $decoded = trim($decoded);
        $decoded = preg_replace('/\s+$/', '', $decoded);
        $decoded = rtrim($decoded, "\n\r");
    }
    return $decoded ? $decoded : $text;
}

$databasePath = __DIR__ . '/database.db';

if (!file_exists($databasePath)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'دیتابیس یافت نشد'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = new PDO("sqlite:{$databasePath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'خطا در اتصال به دیتابیس'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$types = [
    'truth_normal_boy',
    'truth_normal_girl',
    'truth_sexy_boy',
    'truth_sexy_girl',
    'dare_normal_boy',
    'dare_normal_girl',
    'dare_sexy_boy',
    'dare_sexy_girl'
];

$result = [];

foreach ($types as $type) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE type = :type ORDER BY RANDOM() LIMIT 1");
    $stmt->execute([':type' => $type]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($question) {
        $question['question'] = decodeQuestion($question['question']);
        $result[] = $question;
    }
}

echo json_encode([
    'status' => 'success',
    'count' => count($result),
    'data' => $result
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
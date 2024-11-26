<?php
session_start(); // セッションを利用する場合

// ユーザーIDなどで識別
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'ユーザーIDが見つかりません。']);
    exit;
}

try {
    // DB接続
    $pdo = new PDO('mysql:host=localhost;dbname=your_db_name;charset=utf8', 'your_username', 'your_password');

    // なめこを全て生やす（例として、'grown' 状態を更新）
    $sql = "UPDATE namekos SET status = 'grown' WHERE user_id = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

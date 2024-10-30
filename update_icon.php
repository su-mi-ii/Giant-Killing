<?php
session_start();
require 'db-connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];

// POSTデータから新しいアイコンのパスを取得
$new_icon = $_POST['new_icon'] ?? '';

// 新しいアイコンをデータベースに保存
if ($new_icon) {
    $sql_update = "UPDATE zukan SET character_image = ? WHERE user_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_icon, $user_id]);
}

http_response_code(200); // 成功レスポンス

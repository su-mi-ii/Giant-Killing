<?php
require 'db-connect.php';
session_start();

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

// 今日の視聴回数を取得
$sql = "SELECT views FROM ad_views WHERE user_id = :user_id AND date = :date";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':date', $current_date, PDO::PARAM_STR);
$stmt->execute();
$ad_view = $stmt->fetch(PDO::FETCH_ASSOC);

if ($ad_view) {
    // 既存のレコードがある場合は更新
    $sql = "UPDATE ad_views SET views = views + 1 WHERE user_id = :user_id AND date = :date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $current_date, PDO::PARAM_STR);
} else {
    // 新しいレコードを挿入
    $sql = "INSERT INTO ad_views (user_id, date, views) VALUES (:user_id, :date, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $current_date, PDO::PARAM_STR);
}
$stmt->execute();

// 広告ページにリダイレクト!
header('Location: MiyamotoOp.php');
exit();
?>

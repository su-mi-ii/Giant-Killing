<?php
// register.php

require 'db-connect.php';

// セッションを開始
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    // バリデーション
    if (!empty($user_name) && !empty($password)) {
        // パスワードをハッシュ化
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // DBに登録
            $sql = "INSERT INTO users (user_name, password) VALUES (:user_name, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            // 登録したユーザーのIDを取得
            $user_id = $pdo->lastInsertId();

            // セッションにユーザーIDを保存
            $_SESSION['user_id'] = $user_id;

            // クッキーにユーザーIDを保存（例: 30日間有効）
            setcookie('user_id', $user_id, time() + (30 * 24 * 60 * 60), '/', '', false, true);

            // 登録完了後 top.php へリダイレクト
            header('Location: menu.php');
            exit;
        } catch (PDOException $e) {
            // エラーメッセージ
            $error_message = '登録中にエラーが発生しました。';
        }
    } else {
        $error_message = '全てのフィールドを入力してください。';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
</head>
<body>
    <h1>新規登録</h1>
    <?php if (isset($error_message)) { echo '<p style="color:red;">' . htmlspecialchars($error_message) . '</p>'; } ?>
    <form method="post">
        <label for="user_name">ユーザー名:</label>
        <input type="text" name="user_name" id="user_name" required>
        <br>
        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">登録</button>
    </form>
    <form action="menu.php" method="get">
        <button type="submit">戻る</button>
    </form>
</body>
</html>

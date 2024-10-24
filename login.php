<?php
// login.php

require 'db-connect.php';

// セッションを開始
session_start();

// クッキーがセットされているか確認
if (isset($_COOKIE['user_id'])) {
    // クッキーが存在すれば、セッションにユーザーIDを設定し top.php へリダイレクト
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    header('Location: top.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    // バリデーション
    if (!empty($user_name) && !empty($password)) {
        try {
            // DBからユーザーを取得
            $sql = "SELECT user_id, password FROM users WHERE user_name = :user_name";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // クッキーにユーザーIDを保存（例: 30日間有効）
                setcookie('user_id', $user['user_id'], time() + (30 * 24 * 60 * 60), '/', '', false, true);

                // セッションにユーザーIDを保存
                $_SESSION['user_id'] = $user['user_id'];

                // ログイン成功後 top.php へリダイレクト
                header('Location: top.php');
                exit;
            } else {
                $error_message = 'ユーザー名かパスワードが違います。';
            }
        } catch (PDOException $e) {
            $error_message = 'ログイン中にエラーが発生しました。';
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
    <title>ログイン</title>
    <style>
     /* リセットスタイル */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

h1 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

form {
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #555;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-bottom: 10px;
}

button:hover {
    background-color: #45a049;
}

p {
    font-size: 14px;
    color: red;
    margin-bottom: 15px;
    text-align: center;
}

button[type="submit"] {
    background-color: #4CAF50;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

button[type="button"] {
    background-color: #555;
}

button[type="button"]:hover {
    background-color: #333;
}

        </style>
</head>
<body>
    <div class="container">
        <h1>ログイン</h1>
        <form method="post">
            <label for="user_name">ユーザー名</label>
            <input type="text" name="user_name" id="user_name" required>
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" required>
            <?php if (!empty($error_message)) { echo '<p>' . htmlspecialchars($error_message) . '</p>'; }?>

            <button type="submit">ログイン</button>
        </form>
        <form action="menu.php">
    <button type="button" onclick="window.location.href='menu.php';">戻る</button>
</form>
    </div>
</body>
</html>

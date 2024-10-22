<?php
<<<<<<< HEAD
// login_process.php
session_start();

// データベース接続
require 'db-connect.php'; // パスは `db-connect.php` の位置に応じて調整してください

// フォームデータの取得とバリデーション
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 簡単なバリデーション
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'ユーザー名とパスワードは必須です。';
        header('Location: login.php');
        exit;
    }

    try {
        // ユーザー情報の取得
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // 認証成功
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: login_success.php');
            exit;
        } else {
            // 認証失敗
            $_SESSION['error'] = 'ユーザー名またはパスワードが正しくありません。';
            header('Location: login.php');
            exit;
        }

    } catch (PDOException $e) {
        // 本番環境では詳細なエラーメッセージを表示しないでください
        $_SESSION['error'] = 'ログイン中にエラーが発生しました。';
        header('Location: login.php');
        exit;
    }
} else {
    // 不正なアクセス
    header('Location: login.php');
    exit;
}
?>

=======
// login.php

require 'db-connect.php';

// セッションを開始
session_start();

// セッションがセットされているか確認
if (isset($_SESSION['user_id'])) {
    // セッションが存在すれば top.php へリダイレクト
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
</head>
<body>
    <h1>ログイン</h1>
    <?php if (!empty($error_message)) { echo '<p style="color:red;">' . htmlspecialchars($error_message) . '</p>'; } ?>
    <form method="post">
        <label for="user_name">ユーザー名:</label>
        <input type="text" name="user_name" id="user_name" required>
        <br>
        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">ログイン</button>
    </form>
    <form action="menu.php" method="get">
        <button type="submit">戻る</button>
    </form>
</body>
</html>
>>>>>>> main

<?php
// register_process.php
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
        header('Location: register.php');
        exit;
    }

    // パスワードのハッシュ化
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // ユーザー名の重複確認
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = '既に存在するユーザー名です。';
            header('Location: register.php');
            exit;
        }

        // ユーザーの挿入
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $password_hash]);

        // 登録成功後、ログインページへリダイレクト
        header('Location: login.php?register=success');
        exit;

    } catch (PDOException $e) {
        // 本番環境では詳細なエラーメッセージを表示しないでください
        $_SESSION['error'] = '登録中にエラーが発生しました。';
        header('Location: register.php');
        exit;
    }
} else {
    // 不正なアクセス
    header('Location: register.php');
    exit;
}
?>


<?php
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


<?php
// login_success.php
session_start();

// ログイン状態の確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ユーザー名の取得
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン成功</title>
    <style>
        /* スタイルは以前と同じ */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .button {
            padding: 10px 20px;
            font-size: 1rem;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ログイン成功！</h1>
        <p>ようこそ、<?php echo $username; ?>さん！</p>
        <button class="button" onclick="window.location.href='logout.php'">ログアウト</button>
    </div>
</body>
</html>

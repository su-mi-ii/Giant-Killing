<?php
require 'db-connect.php';
session_start(); // セッションを開始

// ログインしているユーザーのIDを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // ユーザーごとの道具情報を取得
    $sql = "SELECT tool_name, price, effect, tool_image FROM tools WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo 'ログインが必要です。';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>栽培道具</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            background: linear-gradient(to bottom, #e0d4b0, #a69362);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 30px;
            padding: 40px;
            border-radius: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .exit-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
        }

        .exit-button img {
            width: 50px;
            height: auto;
            filter: drop-shadow(2px 2px 2px rgba(0, 0, 0, 0.5));
        }

        .upgrade-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            width: 150px;
            height: 220px;
            border: 2px solid #b08a3c;
            border-radius: 10px;
            background-color: #fff9e0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .upgrade-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .upgrade-item img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        .upgrade-item h3 {
            font-size: 1.2rem;
            color: #b08a3c;
            margin-bottom: 5px;
        }

        .upgrade-item p {
            font-size: 0.9rem;
            color: #555;
            margin: 0;
        }

        .upgrade-button {
            margin-top: 10px;
            padding: 8px 15px;
            border: none;
            background-color: #d4af37;
            color: #fff;
            font-size: 0.9rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .upgrade-button:hover {
            background-color: #c4962e;
        }
    </style>
</head>
<body>

    <button class="exit-button">
        <img src="exit_icon.png" alt="Exit">
    </button>

    <div class="container">
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <div class="upgrade-item">
                    <img src="<?= htmlspecialchars($tool['tool_image']) ?>" alt="<?= htmlspecialchars($tool['tool_name']) ?> Icon">
                    <h3><?= htmlspecialchars($tool['tool_name']) ?></h3>
                    <p><?= htmlspecialchars($tool['effect']) ?></p>
                    <button class="upgrade-button"><?= htmlspecialchars($tool['price']) ?>cでレベルアップ</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>このユーザーには道具がありません。</p>
        <?php endif; ?>
    </div>

</body>
</html>

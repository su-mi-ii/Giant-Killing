<?php
// db-connect.php の読み込みとセッションの開始
require 'db-connect.php';
session_start();

// ログインしているユーザーのIDを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // ユーザーのアイテム情報を取得
    $sql = "SELECT item_id, item_name, price, effect, item_image, level FROM items WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 広告消去アイテムが購入済みか確認
    $sql = "SELECT level FROM items WHERE user_id = :user_id AND item_name = 'バナー広告消去権'";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $adRemovalItem = $stmt->fetch(PDO::FETCH_ASSOC);
    $hasAdRemoval = $adRemovalItem && $adRemovalItem['level'] > 0; // レベルが1以上の場合は購入済み
} else {
    echo 'ログインが必要です。';
    exit;
}

// 購入処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    // 広告消去アイテムが既に購入されていないかチェック
    $sql = "SELECT item_name FROM items WHERE item_id = :item_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item['item_name'] === 'バナー広告消去権' && $hasAdRemoval) {
        echo "すでに広告消去権を購入しています。";
        exit;
    }

    // 通常のアイテム購入処理（価格を1.2倍、レベル+1）
    $sql = "UPDATE items SET price = price * 1.2, level = level + 1 WHERE item_id = :item_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: enviroment.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>栽培環境</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #d0e0f0, #a0b0d0);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 50px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .upgrade-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 2px solid #b08a3c;
            border-radius: 10px;
            background-color: #fff9e0;
            width: 160px;
            text-align: center;
        }

        .upgrade-item img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        .upgrade-item h3 {
            font-size: 1.2rem;
            color: #333;
            margin: 5px 0;
        }

        .upgrade-item p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #666;
        }

        .upgrade-button {
            margin-top: 10px;
            padding: 8px 15px;
            font-size: 0.9rem;
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .upgrade-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="top.php" class="back-button">← 戻る</a>
        <?php foreach ($items as $item): ?>
            <div class="upgrade-item">
                <img src="<?= htmlspecialchars($item['item_image']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
                <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                <p>効果: <?= htmlspecialchars($item['effect']) ?></p>
                <p>現在の価格: <?= htmlspecialchars($item['price']) ?>c</p>
                <p>現在のレベル: <?= htmlspecialchars($item['level']) ?></p>
                <form method="POST">
                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                    <?php if ($item['item_name'] === 'バナー広告消去権' && $hasAdRemoval): ?>
                        <button class="upgrade-button" disabled>永久購入済み</button>
                    <?php else: ?>
                        <button class="upgrade-button">レベルアップ</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

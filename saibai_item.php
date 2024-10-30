<?php
require 'db-connect.php';
session_start(); // セッションを開始

// ログインしているユーザーのIDを取得
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // ユーザーごとの道具情報を取得
    $sql = "SELECT tool_id, tool_name, price, effect, tool_image, level 
            FROM tools WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo 'ログインが必要です。';
    exit;
}

// レベルアップ処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool_id = $_POST['tool_id'];

    // 現在の価格とレベルを取得
    $sql = "SELECT price, level FROM tools WHERE tool_id = :tool_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tool_id', $tool_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tool = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tool) {
        // 価格を1.5倍にしてレベルを+1する
        $new_price = ceil($tool['price'] * 1.5); // 価格を切り上げ
        $new_level = $tool['level'] + 1;

        // データベースを更新
        $sql = "UPDATE tools SET price = :new_price, level = :new_level 
                WHERE tool_id = :tool_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':new_price', $new_price, PDO::PARAM_INT);
        $stmt->bindParam(':new_level', $new_level, PDO::PARAM_INT);
        $stmt->bindParam(':tool_id', $tool_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: saibai_item.php'); // ページを再読み込み
        exit;
    }
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
            font-family: 'Noto Sans JP', sans-serif;
            background: linear-gradient(to bottom, #f5f7fa, #c3cfe2);
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
            align-items: center;
            gap: 20px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 1000px;
        }

        .upgrade-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            width: 180px;
            border: 2px solid #b08a3c;
            border-radius: 10px;
            background-color: #fff9e0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .upgrade-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .upgrade-item img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .upgrade-item h3 {
            font-size: 1.2rem;
            color: #b08a3c;
            margin: 5px 0;
            font-weight: bold;
        }

        .upgrade-item p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #555;
        }

        .upgrade-button {
            margin-top: 10px;
            padding: 8px 15px;
            font-size: 0.9rem;
            background-color: #d4af37;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .upgrade-button:hover {
            background-color: #c4962e;
            transform: translateY(-2px);
        }

        .exit-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        .exit-button img {
            width: 40px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="top.php" class="back-button">← 戻る</a>
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <div class="upgrade-item">
                    <img src="<?= htmlspecialchars($tool['tool_image']) ?>" alt="<?= htmlspecialchars($tool['tool_name']) ?> Icon">
                    <h3><?= htmlspecialchars($tool['tool_name']) ?></h3>
                    <p>効果: <?= htmlspecialchars($tool['effect']) ?></p>
                    <p>現在の価格: <?= htmlspecialchars($tool['price']) ?>c</p>
                    <p>現在のレベル: <?= htmlspecialchars($tool['level']) ?></p>
                    <form method="POST">
                        <input type="hidden" name="tool_id" value="<?= $tool['tool_id'] ?>">
                        <button class="upgrade-button">レベルアップ</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>このユーザーには道具がありません。</p>
        <?php endif; ?>
    </div>

</body>
</html>

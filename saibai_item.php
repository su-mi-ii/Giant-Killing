<?php
require 'db-connect.php';
session_start(); // セッションを開始

// ログインユーザー情報
$user_id = $_SESSION['user_id'];

// 所持金を取得
$sql = "SELECT money FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$totalMoney = $user['money'] ?? 0;
$_SESSION['total_money'] = $totalMoney;

// 道具情報を取得
$sql = "SELECT tool_id, tool_name, price, effect, tool_image, level FROM tools WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

// エラーメッセージを保持
$errorMessages = [];

// 購入処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tool_id = $_POST['tool_id'];

    // 選択した道具情報を取得
    $sql = "SELECT price, level, effect FROM tools WHERE tool_id = :tool_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tool_id', $tool_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tool = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tool) {
        if ($tool['level'] >= 10) {
            // レベルが10以上の場合はエラーメッセージを設定
            $errorMessages[$tool_id] = "これ以上レベルを上げることができません。";
        } elseif ($totalMoney >= $tool['price']) {
            // 所持金を更新
            $newMoney = $totalMoney - $tool['price'];
            $sql = "UPDATE users SET money = :newMoney WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':newMoney', $newMoney, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // 道具のレベルと価格を更新
            $newLevel = $tool['level'] + 1;
            $newPrice = ceil($tool['price'] * 1.5);
            $sql = "UPDATE tools SET level = :newLevel, price = :newPrice WHERE tool_id = :tool_id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':newLevel', $newLevel, PDO::PARAM_INT);
            $stmt->bindParam(':newPrice', $newPrice, PDO::PARAM_INT);
            $stmt->bindParam(':tool_id', $tool_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // 出現速度の更新（1.1倍ごとに増加）
            if ($tool['effect'] === '発生速度上昇') {
                $appearanceRate = 1 + (0.1 * $newLevel); // レベルに応じて0.1倍ずつ増加
                $_SESSION['appearance_rate'] = min($appearanceRate, 2.0); // 最大2.0倍まで制限
            }

            // セッションの所持金の値を更新
            $_SESSION['total_money'] = $newMoney;

            // リダイレクトしてリフレッシュ
            header('Location: saibai_item.php');
            exit;
        } else {
            // 所持金が不足している場合
            $errorMessages[$tool_id] = "所持金が不足しています。";
        }
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
            background: #8B4513;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .wallet-container {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ffcf33;
            padding: 10px 20px;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            z-index: 10;
        }

        .wallet-container img {
            width: 24px;
            height: 24px;
            margin-right: 8px;
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
            margin-top: 80px;
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

        .error-message {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 8px 12px;
            font-size: 1rem;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
            z-index: 10;
        }

        .back-button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <!-- 所持金表示とトップへのリンク -->
    <a href="top.php" class="back-button">← トップへ戻る</a>
    <div class="wallet-container">
        <img src="image/coin_icon.png" alt="Coin Icon"> <!-- アイコンを所持金の横に表示 -->
        所持金: <?php echo htmlspecialchars($_SESSION['total_money']); ?>c
    </div>

    <div class="container">
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
                <?php if (isset($errorMessages[$tool['tool_id']])): ?>
                    <p class="error-message"><?= $errorMessages[$tool['tool_id']] ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

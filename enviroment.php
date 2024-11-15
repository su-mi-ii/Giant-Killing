<?php
require 'db-connect.php';
session_start();

// ログインユーザー情報取得
$user_id = $_SESSION['user_id'];

// ユーザーの所持金を取得
$sql = "SELECT money FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$totalMoney = $user['money'] ?? 0;
$_SESSION['total_money'] = $totalMoney;

// アイテム情報を取得
$sql = "SELECT item_id, item_name, price, effect, item_image, level FROM items WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// エラーメッセージをアイテムごとに保持
$errorMessages = [];

// 購入処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    // 選択したアイテム情報を取得
    $sql = "SELECT price, effect, level, item_name FROM items WHERE item_id = :item_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item && $totalMoney >= $item['price'] && $item['level'] == 0) {
        // 所持金を更新
        $newMoney = $totalMoney - $item['price'];
        $sql = "UPDATE users SET money = :newMoney WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':newMoney', $newMoney, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // アイテムのレベルを1（購入済み）に更新
        $sql = "UPDATE items SET level = 1 WHERE item_id = :item_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // レア薬購入処理
        if ($item['item_name'] === 'レア薬') {
            $sql = "UPDATE users SET rare_drug_purchased = 1 WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION['rare_drug_purchased'] = true; // セッションにも反映
        }

        // 成長速度の効果を反映する例
        if ($item['effect'] === '成長速度上昇') {
            $_SESSION['growth_rate'] = ($_SESSION['growth_rate'] ?? 1) * 1.05; // 5%アップ
        }

        // 特定アイテム購入時にワールドアンロック
        if ($item['item_name'] === 'ウチヤマワールド' || $item['item_name'] === 'ディズニーワールド') {
            $world_type = ($item['item_name'] === 'ウチヤマワールド') ? 'utiyama' : 'disney';
            
            // ユーザーがすでにこのワールドを持っているか確認
            $check_world_sql = "SELECT COUNT(*) FROM world WHERE user_id = :user_id AND world_type = :world_type";
            $check_stmt = $pdo->prepare($check_world_sql);
            $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $check_stmt->bindParam(':world_type', $world_type);
            $check_stmt->execute();
            $world_exists = $check_stmt->fetchColumn();

            // ワールドが未登録の場合のみ追加
            if (!$world_exists) {
                $world_sql = "INSERT INTO world (user_id, world_type) VALUES (:user_id, :world_type)";
                $world_stmt = $pdo->prepare($world_sql);
                $world_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $world_stmt->bindParam(':world_type', $world_type);
                $world_stmt->execute();
            }
        }
        
        $_SESSION['total_money'] = $newMoney;
        header('Location: enviroment.php');
        exit;
    } elseif ($item['level'] > 0) {
        // 購入済みの場合のエラーメッセージ
        $errorMessages[$item_id] = "このアイテムはすでに購入済みです。";
    } else {
        // 所持金が不足している場合
        $errorMessages[$item_id] = "所持金が不足しています。";
    }
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
            background: #8B4513; /* 背景色を茶色に */
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
            display: flex;
            align-items: center; /* テキストとアイコンを垂直方向に中央揃え */
            z-index: 10;
        }

        .wallet-container img {
            width: 24px;
            height: 24px;
            margin-right: 8px;
            vertical-align: middle; /* アイコンをテキストと中央揃え */
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 50px;
            padding: 40px;
            margin-top: 80px;
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
            transition: transform 0.3s;
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
            transition: background-color 0.3s;
        }

        .upgrade-button:hover {
            background-color: #45a049;
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
<a href="top.php" class="back-button">← トップへ戻る</a>
    <div class="wallet-container">
        <img src="image/coin_kinoko.png" alt="Coin Icon"> <!-- アイコンを所持金の横に表示 -->
        所持金: <?php echo htmlspecialchars($_SESSION['total_money']); ?>c
    </div>

    <div class="container">
        <?php foreach ($items as $item): ?>
            <div class="upgrade-item">
                <img src="<?= htmlspecialchars($item['item_image']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
                <h3><?= htmlspecialchars($item['item_name']) ?></h3>
                <p>効果: <?= htmlspecialchars($item['effect']) ?></p>
                <p>価格: <?= htmlspecialchars($item['price']) ?>c</p>
                <?php if ($item['level'] > 0): ?>
                    <!-- 購入済みのメッセージ表示 -->
                    <p>このアイテムは購入済みです。</p>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                        <button class="upgrade-button">購入</button>
                    </form>
                    <?php if (isset($errorMessages[$item['item_id']])): ?>
                        <p class="error-message"><?= $errorMessages[$item['item_id']] ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
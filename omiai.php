<?php
// データベース接続
require 'db-connect.php';

// 自分のキャラクター情報を取得
$self_sql = "
    SELECT c.name, c.character_image, c.HP, c.speed, c.attack_type, c.rarity
    FROM party p
    JOIN characters c ON p.character_id = c.character_id
    WHERE p.user_id = :user_id
    ORDER BY p.position ASC
";
$self_stmt = $pdo->prepare($self_sql);
$self_stmt->execute(['user_id' => 1]); // 自分のユーザーID
$self_characters = $self_stmt->fetchAll(PDO::FETCH_ASSOC);

// 敵のキャラクター情報を取得
$enemy_sql = "
    SELECT c.name, c.character_image, ep.HP, ep.speed, c.attack_type, c.rarity
    FROM enemy_party ep
    JOIN characters c ON ep.character_id = c.character_id
    ORDER BY ep.position ASC
";
$enemy_stmt = $pdo->prepare($enemy_sql);
$enemy_stmt->execute();
$enemy_characters = $enemy_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バトル画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .battle-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;
            margin-bottom: 20px;
        }
        .team {
            text-align: center;
            width: 45%;
        }
        .team h3 {
            margin-bottom: 10px;
        }
        .team .rarity {
            color: #888;
            font-size: 0.9em;
        }
        .team .icons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .team .icons img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #ddd;
        }
        .button-container {
            text-align: center;
        }
        .button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            background-color: #45a049;
        }
        #countdown {
            margin-top: 10px;
            font-size: 1.2em;
            color: #555;
        }
    </style>
</head>
<body>

<div class="battle-container">
    <!-- 自分のチーム -->
    <div class="team">
        <h3><?= htmlspecialchars($self_characters[0]['name']) ?></h3>
        <p class="rarity">レア度: <?= htmlspecialchars($self_characters[0]['rarity']) ?></p>
        <div class="icons">
            <?php foreach ($self_characters as $character): ?>
                <img src="<?= htmlspecialchars($character['character_image']) ?>" alt="<?= htmlspecialchars($character['name']) ?>">
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 敵のチーム -->
    <div class="team">
        <h3><?= htmlspecialchars($enemy_characters[0]['name']) ?></h3>
        <p class="rarity">レア度: <?= htmlspecialchars($enemy_characters[0]['rarity']) ?></p>
        <div class="icons">
            <?php foreach ($enemy_characters as $character): ?>
                <img src="<?= htmlspecialchars($character['character_image']) ?>" alt="<?= htmlspecialchars($character['name']) ?>">
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- バトル開始ボタンとカウントダウン -->
<div class="button-container">
    <button class="button" onclick="startBattle()">バトル開始</button>
    <p id="countdown"></p>
</div>

<script>
let countdown = 3;

function startBattle() {
    let countdownElement = document.getElementById('countdown');
    let interval = setInterval(() => {
        countdownElement.textContent = countdown + "秒後にバトル開始";
        countdown--;
        if (countdown < 0) {
            clearInterval(interval);
            window.location.href = "battle_1.php";  // バトル画面に遷移
        }
    }, 1000);
}
</script>

</body>
</html>

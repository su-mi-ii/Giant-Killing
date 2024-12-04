<?php
// データベース接続
require 'db-connect.php';

session_start();

// **戦闘データのリセット**（毎回実行）
unset($_SESSION['battle_data']);

// 敵のキャラクター生成
$random_rarity = rand(1, 5); // ランダムなレアリティ

// 敵チームのキャラクターを取得
$enemy_sql = "
    SELECT c.character_id, c.name, c.character_image, c.HP, c.speed, c.attack_type, c.rarity
    FROM characters c
    WHERE c.rarity = :rarity
    ORDER BY RAND()
    LIMIT 3
";
$enemy_stmt = $pdo->prepare($enemy_sql);
$enemy_stmt->execute(['rarity' => $random_rarity]);
$enemy_characters = $enemy_stmt->fetchAll(PDO::FETCH_ASSOC);

// プレイヤーチームのキャラクターを取得
$self_sql = "
    SELECT c.character_id, c.name, c.character_image, c.HP AS HP_max, c.speed, c.attack_type, c.rarity
    FROM party p
    JOIN characters c ON p.character_id = c.character_id
    WHERE p.user_id = :user_id
    ORDER BY p.position ASC
";

$self_stmt = $pdo->prepare($self_sql);
$self_stmt->execute(['user_id' => $_SESSION['user_id'] ?? 1]);
$self_characters = $self_stmt->fetchAll(PDO::FETCH_ASSOC);

// データチェック
if (empty($self_characters)) {
    die("プレイヤーキャラクターが見つかりません。編成画面でキャラクターを選択してください。");
}

if (empty($enemy_characters)) {
    die("敵キャラクターが見つかりません。データベースを確認してください。");
}

// プレイヤーキャラクターにスキルを割り当て
foreach ($self_characters as &$character) {
    $skill_query = $pdo->prepare("SELECT * FROM skills WHERE type = :type");
    $skill_query->execute(['type' => $character['attack_type']]);
    $character['skills'] = $skill_query->fetchAll(PDO::FETCH_ASSOC);
}

// 敵キャラクターにスキルを割り当て
foreach ($enemy_characters as &$character) {
    $skill_query = $pdo->prepare("SELECT * FROM skills WHERE type = :type");
    $skill_query->execute(['type' => $character['attack_type']]);
    $character['skills'] = $skill_query->fetchAll(PDO::FETCH_ASSOC);
}

// プレイヤーチームと敵チームの初期HP設定
foreach ($self_characters as &$character) {
    $character['HP'] = $character['HP_max'];
}
foreach ($enemy_characters as &$character) {
    $character['HP_max'] = $character['HP']; // 敵のHP_maxを初期化
}

// 戦闘データの初期化
$_SESSION['battle_data'] = [
    'player_team' => $self_characters,
    'enemy_team' => $enemy_characters,
    'player_front' => 0,
    'enemy_front' => 0,
    'turn_order' => array_merge($self_characters, $enemy_characters),
    'current_turn' => 0,
    'logs' => [], // バトルログを初期化
];

// 行動順をスピードでソート
usort($_SESSION['battle_data']['turn_order'], function ($a, $b) {
    return $b['speed'] - $a['speed'];
});

$user_name = '自分のチーム'; // デフォルト値
if (isset($_SESSION['user_id'])) {
    $user_sql = "SELECT user_name FROM users WHERE user_id = :user_id";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->execute(['user_id' => $_SESSION['user_id']]);
    $user_result = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if ($user_result) {
        $user_name = htmlspecialchars($user_result['user_name']);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>敵情報画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #2c3e50;
            color: #ecf0f1;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        h1 {
            margin: 20px;
            color: #f1c40f;
        }
        .battle-container {
            display: flex;
            justify-content: space-around;
            padding: 20px;
        }
        .team {
            background: #34495e;
            border-radius: 10px;
            padding: 20px;
            width: 37%;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.5);
        }
        .team h3 {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #ecf0f1;
        }
        .character-box {
            display: flex;
            align-items: center;
            background: #2c3e50;
            border-radius: 10px;
            padding: 10px;
            margin: 10px auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .character-box img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            margin-right: 20px;
            border: 2px solid #ecf0f1;
        }
        .character-stats {
            text-align: left;
            flex: 1;
        }
        .character-stats p {
            margin: 5px 0;
            font-size: 14px;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px; /* ボタン間のスペース */
            margin-top: 20px; /* ボタンの位置調整 */
        }

        .button {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .button:hover {
            background: #c0392b;
            transform: scale(1.05); /* ボタンが少し大きくなる */
        }
        .hp-bar {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            overflow: hidden;
            width: 100%;
            height: 10px;
            margin-top: 5px;
        }
        .hp-bar-fill {
            height: 100%;
            background: #e74c3c;
            transition: width 0.3s ease;
        }
        /* モーダルスタイル */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #34495e;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            z-index: 10;
            text-align: center;
        }
        .modal h3 {
            margin-bottom: 15px;
        }
        .modal button {
            margin: 5px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 5;
        }
    </style>
</head>
<body>
<h1>戦闘前画面</h1>
<div class="battle-container">
    <!-- プレイヤーチーム -->
    <div class="team">
        <h3><?= "{$user_name}のチーム"?></h3>
        <?php foreach ($self_characters as $character): ?>
            <div class="character-box">
                <img src="<?= htmlspecialchars($character['character_image']) ?>" alt="<?= htmlspecialchars($character['name']) ?>">
                <div class="character-stats">
                    <p><strong>レアリティ:</strong> <?= htmlspecialchars($character['rarity']) ?></p>
                    <p><strong>名前:</strong> <?= htmlspecialchars($character['name']) ?></p>
                    <p><strong>タイプ:</strong> <?= htmlspecialchars($character['attack_type']) ?></p>
                    <p><strong>HP:</strong> <?= htmlspecialchars($character['HP']) ?></p>
                    <p><strong>スピード:</strong> <?= htmlspecialchars($character['speed']) ?></p>
                    <p><strong>特技:</strong></p>
                    <ul>
                        <?php foreach ($character['skills'] as $skill): ?>
                            <li><?= htmlspecialchars($skill['name']) ?> - <?= htmlspecialchars($skill['effect']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="overlay" id="overlay"></div>
    <div class="button-container">
        <form method="POST" action="battle.php">
            <button class="button" type="submit">ゲーム開始</button>
        </form>
        <form method="GET" action="start.php">
            <button class="button" type="button" onclick="window.location.href='start.php'">タイトルへ</button>
        </form>
    </div>

    <!-- 敵チーム -->
    <div class="team">
        <h3><?= "レア度☆{$random_rarity}の敵チーム" ?></h3>
        <?php foreach ($enemy_characters as $character): ?>
            <div class="character-box">
                <img src="<?= htmlspecialchars($character['character_image']) ?>" alt="☆<?= htmlspecialchars($character['rarity']) ?>の敵">
                <div class="character-stats">
                    <p><strong>レアリティ:</strong> <?= htmlspecialchars($character['rarity']) ?></p>
                    <p><strong>名前:</strong> <?= htmlspecialchars($character['name']) ?></p>
                    <p><strong>タイプ:</strong> <?= htmlspecialchars($character['attack_type']) ?></p>
                    <p><strong>HP:</strong> <?= htmlspecialchars($character['HP']) ?></p>
                    <p><strong>スピード:</strong> <?= htmlspecialchars($character['speed']) ?></p>
                    <p><strong>特技:</strong></p>
                    <ul>
                        <?php foreach ($character['skills'] as $skill): ?>
                            <li><?= htmlspecialchars($skill['name']) ?> - <?= htmlspecialchars($skill['effect']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<iframe src="btlbgm_player.php" style="display:none;" id="bgm-frame"></iframe>
<script>
    // スクリプト
</script>
</body>
</html>

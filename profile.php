<?php
session_start();
require 'db-connect.php';

if (!isset($_SESSION['user_id'])) {
    echo 'ユーザーがログインしていません。';
    exit;
}

$user_id = $_SESSION['user_id'];

$sql_user = "SELECT user_name FROM users WHERE user_id = ?";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$user_name = $user ? $user['user_name'] : 'Unknown User';

$sql_icon = "
    SELECT z.character_image 
    FROM zukan z 
    WHERE z.user_id = ? 
    LIMIT 1
";
$stmt_icon = $pdo->prepare($sql_icon);
$stmt_icon->execute([$user_id]);
$icon = $stmt_icon->fetch(PDO::FETCH_ASSOC);

$character_image = $icon ? $icon['character_image'] : 'default_icon.png';

$sql_harvest = "
    SELECT COUNT(DISTINCT character_id) AS discovered_characters, COUNT(*) AS total_harvest 
    FROM harvest_log 
    WHERE user_id = ?
";
$stmt_harvest = $pdo->prepare($sql_harvest);
$stmt_harvest->execute([$user_id]);
$harvest = $stmt_harvest->fetch(PDO::FETCH_ASSOC);

$discovered_characters = $harvest['discovered_characters'] ?? 0;
$total_harvest = $harvest['total_harvest'] ?? 0;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ningen License Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #d6d6d6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card-container {
            background-color: #c2a562;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
        }
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }
        .icon-image {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .info {
            margin: 10px 0;
            font-size: 1.2rem;
            color: #fff;
        }
        .harvest-info {
            margin-top: 15px;
            background-color: #8b5e34;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
        }
    </style>
</head>
<body>

    <!-- Back Button -->
    <a href="top.php" class="back-button">← 戻る</a>

    <!-- Ningen License Card -->
    <div class="card-container">
        <h2>Ningen License Card</h2>

        <!-- User Icon -->
        <img src="<?= htmlspecialchars($character_image) ?>" alt="User Icon" class="icon-image">

        <!-- User Name -->
        <div class="info"><?= htmlspecialchars($user_name) ?></div>

        <!-- Harvest Info -->
        <div class="harvest-info">
            <div>発見した人間: <?= htmlspecialchars($discovered_characters) ?>人</div>
            <div>収穫総数: <?= htmlspecialchars($total_harvest) ?>人</div>
        </div>
    </div>

</body>
</html>

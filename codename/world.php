<?php
require 'db-connect.php';

$sql = "SELECT world_id, world_type FROM world";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$worlds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ワールド選択</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .container {
            display: inline-block;
            border: 2px solid #ccc;
            padding: 20px;
            border-radius: 10px;
        }

        .world-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            margin: 10px 0;
            background-color: #f0f0f0;
        }

        .world-option img {
            width: 50px;
            height: auto;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .navigation-buttons {
            margin-top: 20px;
        }

        .nav-button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 2rem;
        }

        .nav-button img {
            width: 40px;
            height: auto;
        }

    </style>
</head>
<body>

    <button class="back-button">戻る</button>

    <h1>ワールド選択</h1>

    <div class="container">
        <?php foreach ($worlds as $world): ?>
            <div class="world-option">
                <span><?= htmlspecialchars($world['world_name']) ?></span>
                <img src="<?= htmlspecialchars($world['world_image']) ?>" alt="<?= htmlspecialchars($world['world_name']) ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <div class="navigation-buttons">
        <button class="nav-button">
            <img src="left_arrow.png" alt="Left">
        </button>
        <button class="nav-button">
            <img src="right_arrow.png" alt="Right">
        </button>
    </div>

</body>
</html>

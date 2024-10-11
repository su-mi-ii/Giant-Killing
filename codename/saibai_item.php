<?php
// データベース接続
require 'db-connect.php';

// 道具データを取得するクエリ
$sql = "SELECT tool_name, price, tool_image FROM tools";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>栽培道具</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .item {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            width: 150px;
            text-align: center;
        }

        .item img {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .item p {
            margin: 5px 0;
            font-size: 1rem;
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

    <!-- Exit button -->
    <button class="exit-button">
        <img src="exit_icon.png" alt="Exit">
    </button>

    <h1>栽培道具</h1>

    <div class="container">
        <?php foreach ($tools as $tool): ?>
            <div class="item">
                <img src="<?= htmlspecialchars($tool['tool_image']) ?>" alt="<?= htmlspecialchars($tool['tool_name']) ?>">
                <p><?= htmlspecialchars($tool['tool_name']) ?></p>
                <p><?= htmlspecialchars($tool['price']) ?>C</p>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

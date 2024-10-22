<?php
require 'db-connect.php';

$sql = "SELECT tool_name, price,effect,tool_image FROM tools";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レベルアップ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
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

        .upgrade-item {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .upgrade-item img {
            width: 50px;
            height: auto;
        }

        .upgrade-button {
            padding: 10px 20px;
            border: 2px solid #333;
            background-color: #f4f4f4;
            cursor: pointer;
            font-size: 1rem;
            border-radius: 5px;
        }

        .upgrade-button:hover {
            background-color: #e0e0e0;
        }

    </style>
</head>
<body>

    <button class="exit-button">
        <img src="exit_icon.png" alt="Exit">
    </button>

    <div class="container">
        <?php foreach ($tools as $tool): ?>
            <div class="upgrade-item">
                <img src="<?= htmlspecialchars($tool['tool_image']) ?>" alt="<?= htmlspecialchars($tool['tool_name']) ?> Icon">
                <button class="upgrade-button"><?= htmlspecialchars($tool['price']) ?>cでレベルアップ</button>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

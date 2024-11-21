<?php
require 'db-connect.php';
session_start();

// ログインユーザー情報取得
$user_id = $_SESSION['user_id'];

// ワールドの選択処理
if (isset($_GET['world'])) {
    $selected_world = $_GET['world'];

    // 現在のワールドを更新
    $sql = "UPDATE users SET current_world = :selected_world WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':selected_world', $selected_world, PDO::PARAM_STR);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // ワールドに応じたページにリダイレクト
    if ($selected_world === 'utiyama') {
        header('Location: utiyama_top.php');
        exit;
    } elseif ($selected_world === 'disney') {
        header('Location: disney_top.php');
        exit;
    } elseif ($selected_world === 'default') {
        header('Location: top.php');
        exit;
    }
}

// 現在のワールドを取得
$sql = "SELECT current_world FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$current_world = $stmt->fetchColumn();

// ユーザーが開放したワールドを取得
$sql = "SELECT world_type FROM world WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$unlocked_worlds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ワールド情報を設定
$worlds = [
    'default' => ['name' => 'デフォルトワールド', 'image' => 'image/harry.png'],
    'utiyama' => ['name' => 'ウチヤマ ワールド', 'image' => 'image/☆１内山.png'],
    'disney' => ['name' => 'ディズニー ワールド', 'image' => 'image/ディズニー.png']
];
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
            background: linear-gradient(135deg, #f2f2f2, #ffffff);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #333;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #444;
            text-shadow: 1px 1px 2px #ddd;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            width: 90%;
            max-width: 600px;
        }

        .world-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f7f7f7;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
        }

        .world-option:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
            background-color: #e0e0e0;
        }

        .world-option img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .world-option.current-world {
            background: #e7f7e7;
            border: 1px solid #4CAF50;
        }

        .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #4CAF50;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <button class="back-button" onclick="window.location.href='top.php'">戻る</button>

    <h1>ワールド選択</h1>

    <div class="container">
        <?php foreach ($worlds as $type => $world): ?>
            <?php if (in_array($type, $unlocked_worlds) || $type === 'default'): ?>
                <a href="world.php?world=<?= htmlspecialchars($type) ?>" class="world-option <?= $type === $current_world ? 'current-world' : '' ?>">
                    <span><?= htmlspecialchars($world['name']) ?></span>
                    <img src="<?= htmlspecialchars($world['image']) ?>" alt="<?= htmlspecialchars($world['name']) ?>">
                </a>
            <?php else: ?>
                <div class="world-option disabled">
                    <span><?= htmlspecialchars($world['name']) ?></span>
                    <img src="<?= htmlspecialchars($world['image']) ?>" alt="<?= htmlspecialchars($world['name']) ?>">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <iframe src="bgm.html" style="display:none;" id="bgm-frame"></iframe>
</body>
</html>

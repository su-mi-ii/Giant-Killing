<?php
// DB接続ファイルをインクルード
include 'db-connect.php';

// データベースからプロフィール情報を取得するクエリを実行
try {
    // 任意のユーザーIDを設定（例: 1）
    $user_id = 1;

    // プロフィール情報を取得するSQLクエリ
    $sql = 'SELECT username, profile_image_url, discovered_humans, total_harvested FROM users WHERE id = :user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // 結果を取得
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 取得したデータを変数に格納
        $nickname = $user['username'];
        $profile_image_url = $user['profile_image_url'];
        $discovered_humans = $user['discovered_humans'];
        $total_harvested = $user['total_harvested'];
    } else {
        // デフォルトの値を設定
        $nickname = '未登録';
        $profile_image_url = 'default_profile.png';
        $discovered_humans = 0;
        $total_harvested = 0;
    }
} catch (PDOException $e) {
    echo 'データベースエラー: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ningen License Card</title>
    <style>
        /* 前回のスタイル */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            text-align: center;
        }

        .container {
            width: 400px;
            margin: 50px auto;
            background-color: #d2a679;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .license-card {
            background-color: #c58c3d;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-picture {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #fff;
        }

        .profile-info {
            text-align: left;
            color: #fff;
        }

        .profile-info h2 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .profile-info p {
            margin: 5px 0;
            font-size: 1rem;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        .back-button img {
            width: 40px;
            height: auto;
        }

    </style>
</head>
<body>

    <!-- Back Button -->
    <button class="back-button">
        <img src="back_arrow.png" alt="Back">
    </button>

    <!-- License Card Container -->
    <div class="container">
        <h1>Ningen License Card</h1>

        <div class="license-card">
            <!-- Profile Picture -->
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($profile_image_url); ?>" alt="Profile Picture" style="width: 100%; height: auto; border-radius: 50%;">
            </div>

            <!-- Profile Info -->
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($nickname); ?></h2>
                <p>発見した人間: <?php echo htmlspecialchars($discovered_humans); ?>人</p>
                <p>収穫総数: <?php echo htmlspecialchars($total_harvested); ?>人</p>
            </div>
        </div>
    </div>

</body>
</html>
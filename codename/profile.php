<?php
// データベース接続情報
$servername = "mysql311.phy.lolipop.lan";
$username = "LAA1517492";
$password = "Pass0313"; // 実際のパスワードに置き換えてください
$dbname = "LAA1517492-giants";

// データベース接続の作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続エラーチェック
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // 任意のユーザーIDを設定（例: 1）
    $user_id = 1;

    // プロフィール情報を取得するSQLクエリ
    $sql = 'SELECT username, profile_image_url, discovered_humans, total_harvested FROM users WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // 結果を取得
    $user = $result->fetch_assoc();

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
} catch (Exception $e) {
    echo 'データベースエラー: ' . htmlspecialchars($e->getMessage());
    exit;
}

// データベース接続を閉じる
$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ningen License Card</title>
    <style>
        /* ライセンスカードのスタイル */
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

    <!-- 戻るボタン -->
    <button class="back-button">
        <img src="back_arrow.png" alt="Back">
    </button>

    <!-- ライセンスカードのコンテナ -->
    <div class="container">
        <h1>Ningen License Card</h1>

        <div class="license-card">
            <!-- プロフィール画像 -->
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($profile_image_url); ?>" alt="Profile Picture" style="width: 100%; height: auto; border-radius: 50%;">
            </div>

            <!-- プロフィール情報 -->
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($nickname); ?></h2>
                <p>発見した人間: <?php echo htmlspecialchars($discovered_humans); ?>人</p>
                <p>収穫総数: <?php echo htmlspecialchars($total_harvested); ?>人</p>
            </div>
        </div>
    </div>

</body>
</html>

<<<<<<< HEAD
<?php
session_start(); // セッションを開始

// データベース接続情報
$servername = "mysql311.phy.lolipop.lan";
$username = "LAA1517492";
$password = "Pass0313"; // 実際のパスワードに置き換えてください
$dbname = "LAA1517492-giants";

// 接続を作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続確認
if ($conn->connect_error) {
    die("接続失敗: " . $conn->connect_error);
}

// ユーザーIDをセッションから取得
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id === null) {
    echo "ログインしていません。";
    exit; // ユーザーがログインしていない場合は処理を終了
}

// URLからentry_idを取得
$entry_id = isset($_GET['entry_id']) ? (int)$_GET['entry_id'] : null;

if ($entry_id === null) {
    echo "キャラクターが見つかりませんでした。";
    exit; // entry_idが無効な場合は処理を終了
}

// zukanとcharactersテーブルからキャラクターの詳細を取得
$sql = "SELECT zukan.entry_id, zukan.character_id, zukan.character_image, characters.name, zukan.character_description, characters.rarity 
        FROM zukan 
        JOIN characters ON zukan.character_id = characters.character_id
        WHERE zukan.entry_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $entry_id);
$stmt->execute();
$result = $stmt->get_result();

// 結果確認: 1件のみ取得
if ($result->num_rows === 1) {
    $character = $result->fetch_assoc();
} else {
    echo "キャラクターが見つかりませんでした。";
    exit;
}

// harvest_logテーブルから収穫回数を取得
$harvest_sql = "SELECT COUNT(*) AS harvest_count 
                FROM harvest_log 
                WHERE character_id = ? AND user_id = ?";
$harvest_stmt = $conn->prepare($harvest_sql);
$harvest_stmt->bind_param("ii", $character['character_id'], $user_id);
$harvest_stmt->execute();
$harvest_result = $harvest_stmt->get_result();
$harvest_data = $harvest_result->fetch_assoc();
$harvest_count = $harvest_data['harvest_count'];

// ステートメントを閉じる
$stmt->close();
$harvest_stmt->close();
=======
<?php 
// Database connection details
$servername = "mysql311.phy.lolipop.lan";
$username = "LAA1517492";
$password = "Pass0313"; // Replace with your actual password
$dbname = "LAA1517492-giants";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the entry_id from the URL
$entry_id = $_GET['entry_id'];

// Fetch character details from the zukan and characters tables based on entry_id
$sql = "SELECT zukan.entry_id, zukan.character_id, zukan.harvest_count, zukan.character_image, characters.name, zukan.character_description, characters.rarity 
        FROM zukan 
        JOIN characters ON zukan.character_id = characters.character_id
        WHERE zukan.entry_id = $entry_id";
$result = $conn->query($sql);
$character = $result->fetch_assoc();
>>>>>>> main
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>キャラクター詳細</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }

        .detail-container {
<<<<<<< HEAD
            width: 70%;
=======
            width: 70%; /* Smaller size */
>>>>>>> main
            aspect-ratio: 16/9;
            background: #a37934;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: auto;
        }

        .character-content {
            display: flex;
            align-items: center;
        }

        .character-image {
            width: 250px;
            height: 250px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-right: 15px;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .character-image img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 10px;
            object-fit: contain;
        }

        .character-info {
            max-width: 400px;
            text-align: left;
        }

<<<<<<< HEAD
=======
        /* 名前、レア度、説明に枠を追加 */
>>>>>>> main
        .character-info p {
            font-size: 1em;
            color: #444;
            line-height: 1.4;
            margin: 10px 0;
            padding: 10px;
<<<<<<< HEAD
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
=======
            border: 2px solid #ddd;  /* 枠線を追加 */
            border-radius: 10px;  /* 角を丸くする */
            background-color: #fff; /* 背景色を白に */
        }

        /* 説明部分に下線を追加 */
        .character-info p:nth-child(3) {
            text-decoration: underline; /* 説明部分に下線を追加 */
>>>>>>> main
        }

        .rarity-stars {
            font-size: 1.5em;
            color: gold;
            letter-spacing: 5px;
            margin-bottom: 20px;
            padding: 10px;
<<<<<<< HEAD
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
=======
            border: 2px solid #ddd;  /* 枠線を追加 */
            border-radius: 10px;  /* 角を丸くする */
            background-color: #fff; /* 背景色を白に */
>>>>>>> main
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            background-color: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
        }

        a {
            display: inline-block;
            padding: 8px 15px;
            margin-top: 20px;
            font-size: 1em;
            color: white;
            background-color: #333;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
            transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        a:hover {
            background-color: #1e3c72;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .detail-container {
                width: 90%;
            }

            .character-content {
                flex-direction: column;
                align-items: center;
            }

            .character-image {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .character-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="back-button">
    <a href="zukan.php">←</a>
</div>

<div class="detail-container">
<<<<<<< HEAD
    <h1><?php echo htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <div class="character-content">
        <div class="character-image">
            <img src="<?php echo htmlspecialchars($character['character_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="character-info">
            <p class="rarity-stars">レア度: <?php echo str_repeat('★', $character['rarity']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($character['character_description'], ENT_QUOTES, 'UTF-8')); ?></p>
            <p>収穫回数: <?php echo htmlspecialchars($harvest_count, ENT_QUOTES, 'UTF-8'); ?></p> <!-- 収穫回数を表示 -->
=======
    <h1><?php echo $character['name']; ?></h1>
    <div class="character-content">
        <div class="character-image">
            <img src="<?php echo $character['character_image']; ?>" alt="<?php echo $character['name']; ?>">
        </div>
        <div class="character-info">
            <p class="rarity-stars">レア度: <?php echo str_repeat('★', $character['rarity']); ?></p>
            <p>収穫した数: <?php echo $character['harvest_count']; ?>人</p>
            <p><?php echo $character['character_description']; ?></p>
>>>>>>> main
        </div>
    </div>
</div>

<<<<<<< HEAD
<?php
$conn->close(); // 接続を閉じる
=======

<?php
$conn->close();
>>>>>>> main
?>

</body>
</html>

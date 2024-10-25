<?php 
session_start();

// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    // ログインしていない場合はリダイレクト
    header('Location: login.php');
    exit();
}

// セッションからログイン中のユーザーIDを取得
$user_id = $_SESSION['user_id'];

// データベース接続情報
$servername = "mysql311.phy.lolipop.lan";
$username = "LAA1517492";
$password = "Pass0313"; // 実際のパスワードに置き換えてください
$dbname = "LAA1517492-giants";

// 接続を作成
$conn = new mysqli($servername, $username, $password, $dbname);

// 接続を確認
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ログインしているユーザーのキャラクター情報を取得
$sql = "SELECT zukan.entry_id, zukan.character_id, zukan.character_image, characters.name 
        FROM zukan 
        JOIN characters ON zukan.character_id = characters.character_id
        WHERE zukan.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>図鑑</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
            width: 90%;
            max-width: 1200px;
            background-color: #a37934;
            border-radius: 10px;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .card {
            background-color: white;
            margin: 15px;
            padding: 20px;
            border-radius: 10px;
            width: 250px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: linear-gradient(145deg, #ffffff, #e6e6e6);
            flex-basis: calc(33.333% - 30px); /* カードが一行に3つ表示されるように設定 */
            box-sizing: border-box;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            background-color: #f4f4f4;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            font-size: 18px;
            margin: 10px 0;
            color: #333;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
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
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="back-button">
    <a href="top.php">←</a>
</div>

<div class="container">
    <h1>図鑑</h1>
    <div class="grid">
        <?php
        if ($result->num_rows > 0) {
            
            while($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<a href="character_detail.php?entry_id=' . $row['entry_id'] . '">';
                echo '<img src="' . $row['character_image'] . '" alt="' . $row['character_id'] . '">';
                echo '<h3>' . $row['name'] . '</h3>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo "キャラクターが見つかりません";
        }
        $conn->close();
        ?>
    </div>
</div>

</body>
</html>

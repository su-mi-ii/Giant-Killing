<?php
session_start();
 
// ログインしているか確認
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
 
$user_id = $_SESSION['user_id'];
 
$servername = "mysql311.phy.lolipop.lan";
$username = "LAA1517492";
$password = "Pass0313"; // 実際のパスワードに置き換えてください
$dbname = "LAA1517492-giants";
 
$conn = new mysqli($servername, $username, $password, $dbname);
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f0f0;
            text-align: center;
            margin: 0;
            padding: 0;
        }
 
        .container {
            margin: 20px auto;
            padding: 20px;
            width: 90%;
            max-width: 1200px;
            background-color: #a37934;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
 
        h1 {
            color: #fff;
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
            flex-basis: calc(33.333% - 30px);
            box-sizing: border-box;
            position: relative;
        }
 
        .card:hover {
            transform: translateY(-5px);
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
        }
 
        .back-button {
            position: absolute;
            top: 40px;
            left: 60px;
            background: linear-gradient(135deg, #8b5e34, #a6713d);
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.3s;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
 
        @media (max-width: 768px) {
            .card {
                flex-basis: calc(50% - 30px); /* タブレット用 */
            }
        }
 
        @media (max-width: 480px) {
            .card {
                flex-basis: calc(100% - 30px); /* モバイル用 */
            }
        }
    </style>
</head>
<body>
 
<a href="top.php" class="back-button">← 戻る</a>
 
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
            echo "<p>キャラクターが見つかりません</p>";
        }
        $conn->close();
        ?>
    </div>
</div>
 
</body>
</html>
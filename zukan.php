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

// Fetch character entries from the zukan and characters tables
$sql = "SELECT zukan.entry_id, zukan.character_id, zukan.harvest_count, zukan.character_image, characters.name 
        FROM zukan 
        JOIN characters ON zukan.character_id = characters.character_id";
$result = $conn->query($sql);

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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: contain;  /* 見切れないように contain に変更 */
            border-radius: 10px;
            background-color: #f4f4f4;  /* 背景色を追加して余白部分を目立たせない */
        }
        .card h3 {
            font-size: 18px;
            margin: 10px 0;
        }
        .card p {
            font-size: 14px;
            color: #666;
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
    <a href="previous_page.php">←</a>
</div>

<div class="container">
    <h1>図鑑</h1>
    <div class="grid">
        <?php
        if ($result->num_rows > 0) {
            // Output each entry
            while($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                echo '<img src="' . $row['character_image'] . '" alt="' . $row['character_id'] . '">';
                echo '<h3>' . $row['name'] . '</h3>';  // characters テーブルの name カラムを表示
                echo '<p>収穫した数: ' . $row['harvest_count'] . '人</p>';
                echo '</div>';
            }
        } else {
            echo "No characters found.";
        }
        $conn->close();
        ?>
    </div>
</div>

</body>
</html>

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
            width: 70%; /* Smaller size */
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

        /* 名前、レア度、説明に枠を追加 */
        .character-info p {
            font-size: 1em;
            color: #444;
            line-height: 1.4;
            margin: 10px 0;
            padding: 10px;
            border: 2px solid #ddd;  /* 枠線を追加 */
            border-radius: 10px;  /* 角を丸くする */
            background-color: #fff; /* 背景色を白に */
        }

        /* 説明部分に下線を追加 */
        .character-info p:nth-child(3) {
            text-decoration: underline; /* 説明部分に下線を追加 */
        }

        .rarity-stars {
            font-size: 1.5em;
            color: gold;
            letter-spacing: 5px;
            margin-bottom: 20px;
            padding: 10px;
            border: 2px solid #ddd;  /* 枠線を追加 */
            border-radius: 10px;  /* 角を丸くする */
            background-color: #fff; /* 背景色を白に */
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
    <h1><?php echo $character['name']; ?></h1>
    <div class="character-content">
        <div class="character-image">
            <img src="<?php echo $character['character_image']; ?>" alt="<?php echo $character['name']; ?>">
        </div>
        <div class="character-info">
            <p class="rarity-stars">レア度: <?php echo str_repeat('★', $character['rarity']); ?></p>
            <p>収穫した数: <?php echo $character['harvest_count']; ?>人</p>
            <p><?php echo $character['character_description']; ?></p>
        </div>
    </div>
</div>


<?php
$conn->close();
?>

</body>
</html>

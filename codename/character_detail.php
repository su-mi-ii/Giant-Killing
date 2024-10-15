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
$sql = "SELECT zukan.entry_id, zukan.character_id, zukan.harvest_count, zukan.character_image, characters.name, characters.character_description, characters.rarity 
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
            margin: 0 auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            background-color: #a37934;
            border-radius: 10px;
        }

        .character-image {
            width: 300px;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .rarity-stars {
            font-size: 24px;
            color: gold;
        }
    </style>
</head>
<body>

<div class="detail-container">
    <h1><?php echo $character['name']; ?></h1>
    <img class="character-image" src="<?php echo $character['character_image']; ?>" alt="<?php echo $character['name']; ?>">
    <p class="rarity-stars">レア度: <?php echo str_repeat('★', $character['rarity']); ?></p>
    <p><?php echo $character['character_description']; ?></p>
    <p>収穫した数: <?php echo $character['harvest_count']; ?>人</p>
</div>

<a href="zukan.php">戻る</a>

</body>
</html>

<?php
$conn->close();
?>

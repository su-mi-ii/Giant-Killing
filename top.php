<?php
require 'db-connect.php';
session_start(); // セッションを開始

// キャラクター情報を取得
try {
    $sql = "SELECT name, rarity, character_image, point FROM characters";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'データ取得エラー: ' . htmlspecialchars($e->getMessage());
    exit;
}

// セッションでユーザーの合計ポイントを管理
if (!isset($_SESSION['total_points'])) {
    $_SESSION['total_points'] = 0;
}
$totalPoints = $_SESSION['total_points'];

// なめこ収穫時のPOSTリクエスト処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $characterName = $_POST['name'] ?? '';
    $characterPoint = (int)($_POST['point'] ?? 0); // 収穫したキャラクターのポイント

    try {
        // 収穫ログを保存
        $sql = "INSERT INTO harvest_log (user_id, character_id) 
                VALUES (:user_id, (SELECT character_id FROM characters WHERE name = :name LIMIT 1))";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $characterName, PDO::PARAM_STR);
        $stmt->execute();

        // ポイントを加算し、セッションに保存
        $_SESSION['total_points'] += $characterPoint;
        echo $_SESSION['total_points']; // 合計ポイントを返す
    } catch (PDOException $e) {
        echo 'エラー: ' . htmlspecialchars($e->getMessage());
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>なめこ栽培キッド</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            text-align: center;
            background-image: url('image/gensou.webp');
            background-size: cover; /* 全画面に拡大 */
            background-position: center;
        }

        #nameko-container {
            margin: 20px 0;
            position: relative;
        }

        .nameko {
            font-size: 50px;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            position: absolute;
        }


        .log {
            width: 100%;
            height: 100vh; /* 画面の高さに合わせる */
            position: relative;
            margin: 0 auto;
        }


        .pointbox{
            padding: 0.5em 1em;
            background: -moz-linear-gradient(#ffb03c, #ff708d);
            background: -webkit-linear-gradient(#ffb03c, #ff708d);
            background: linear-gradient(to right, #ffb03c, #ff708d);
            color: #FFF;
            position: absolute;
            right: 20px;

        }

        .pointbox-image {
            float: right;
            position: relative; /* 必要に応じて追加 */
            z-index: 10; /* 他の要素より前面に表示されるように */
            margin-left: auto;
            margin-right: 30px;
            top: 70px;
        }



        #main-button {
            position: absolute;
            left: 20px;
            bottom: 20px;
            width: 120px;
            height: 120px;
            background-color: white;
            border: 2px solid black;
            border-radius: 50%;
            background-image: url('image/tin.png'); /* 1の画像 */
            background-size: cover;
            cursor: pointer;
        }
        .popup {
            position: absolute;
            bottom: 120px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid red;
            display: none;
            cursor: pointer;
            background-size: cover;
        }
        #popup2 {
            left: 10px;
            bottom: 160px;
            background-image: url('image/dan.png'); /* 2の画像 */
        }
        #popup3 {
            left: 125px;
            bottom: 130px;
            background-image: url('image/kana.png'); /* 3の画像 */
        }
        #popup4 {
            left: 170px;
            bottom: 20px;
            background-image: url('image/pro.png'); /* 4の画像 */
        }
    </style>
</head>
<body>
    <!-- 合計ポイント表示 -->
    <div class="pointbox">
        <p>👛　　<?php echo htmlspecialchars($totalPoints); ?> point</p>
    </div>

    <!-- 各種リンク、メインボタン、ポップアップボタン -->
    <div class="pointbox-image">
        <a href="Miyakoku.php"><img src="image/koukoku.webp" alt="広告" width="100" height="100"></a>
        <a href="zukan.php"><img src="image/zukan.webp" alt="図鑑" width="100" height="100"></a>
        <a href="setting.php"><img src="image/setei.webp" alt="設定" width="100" height="100"></a>
    </div>

    <!-- なめこコンテナ -->
    <div id="nameko-container">
        <div class="log"></div>
    </div>

    <div id="container">
        <div id="main-button"></div>
        <div id="popup2" class="popup" onclick="navigateTo('start.php')"></div>
        <div id="popup3" class="popup" onclick="navigateTo('enviroment.php')"></div>
        <div id="popup4" class="popup" onclick="navigateTo('profile.php')"></div>
    </div>

    <script>
        let isVisible = false;
        document.getElementById('main-button').addEventListener('click', function() {
            isVisible = !isVisible;
            togglePopups(isVisible);
        });

        function togglePopups(show) {
            const popups = document.querySelectorAll('.popup');
            popups.forEach(popup => popup.style.display = show ? 'block' : 'none');
        }

        function navigateTo(page) {
            window.location.href = page;
        }

        // PHPからキャラクター情報を取得
        const characters = <?php echo json_encode($characters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let namekos = [];
        const maxNamekos = 24;
        const growthTime = 5000;

        // なめこを成長させる関数
        function growNameko() {
            if (namekos.length < maxNamekos) {
                setTimeout(() => {
                    namekos.push(getRandomCharacter());
                    displayNamekos();
                }, growthTime);
            }
        }

        function getRandomCharacter() {
            const probabilities = characters.map(character => 1 / character.rarity);
            const totalProbability = probabilities.reduce((sum, prob) => sum + prob, 0);
            const normalizedProbabilities = probabilities.map(prob => prob / totalProbability);
            const randomValue = Math.random();
            let cumulativeProbability = 0;

            for (let i = 0; i < normalizedProbabilities.length; i++) {
                cumulativeProbability += normalizedProbabilities[i];
                if (randomValue < cumulativeProbability) return characters[i];
            }
            return characters[0];
        }

        setInterval(growNameko, growthTime + 1000);

                function displayNamekos() {
            const namekoContainer = document.getElementById('nameko-container');
            namekoContainer.innerHTML = '<div class="log"></div>';
            const containerWidth = namekoContainer.offsetWidth;
            const logHeight = window.innerHeight * 0.8;
            const totalColumns = 14;
            const offsetY = 150; // 位置を下げるオフセット（px単位）

            namekos.forEach((nameko, index) => {
                const namekoElement = document.createElement('span');
                const imgElement = document.createElement('img');
                imgElement.src = nameko.character_image;
                imgElement.alt = nameko.name;
                imgElement.title = `${nameko.name} - ${nameko.rarity}`;
                imgElement.style.width = '80px';
                imgElement.style.height = '80px';
                namekoElement.appendChild(imgElement);
                namekoElement.addEventListener('click', () => harvestNameko(index));

                const xPosition = (index % totalColumns) * (containerWidth / (totalColumns + 2));
                const yPosition = logHeight - (100 * Math.floor(index / totalColumns)) - offsetY;
                namekoElement.style.left = `${xPosition}px`;
                namekoElement.style.bottom = `${yPosition}px`;
                namekoElement.style.position = 'absolute';
                namekoContainer.appendChild(namekoElement);
            });
        }


        // なめこを収穫
        function harvestNameko(index) {
            const nameko = namekos[index];
            namekos.splice(index, 1);
            displayNamekos();

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.querySelector('.pointbox p').textContent = `👛 ${xhr.responseText} point`;
                } else {
                    console.error('エラー: サーバーへの収穫ログ送信に失敗しました。');
                }
            };
            xhr.send(`name=${encodeURIComponent(nameko.name)}&point=${nameko.point}`);
        }
    </script>
</body>
</html>

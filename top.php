<?php

// なめこの収穫を記録する関数
function recordHarvest($pdo, $user_id, $character_id) {
    try {
        $sql = "INSERT INTO harvest_log (user_id, character_id) VALUES (:user_id, :character_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':character_id', $character_id);
        $stmt->execute();
    } catch (PDOException $e) {
        echo 'ログ記録エラー: ' . htmlspecialchars($e->getMessage());
    }
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
            height: 460px;
            background-image: url('image/ki.png');
            background-size: cover;
            position: relative;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📷</h1>
        <div id="nameko-container">
            <div class="log"></div>
        </div>
        <div id="message"></div>
    </div>
    <script>
        const growthTime = 5000;
        let namekos = []; // 成長したなめこの配列
        const maxNamekos = 24; // 最大のなめこの数

        // PHPから取得したキャラクター情報をJavaScriptに渡す
        const characters = <?php echo json_encode($characters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const namekoContainer = document.getElementById('nameko-container');
        const message = document.getElementById('message');

        // なめこを自動で育てる関数
        function growNameko() {
            if (namekos.length < maxNamekos) {
                message.textContent = 'なめこが育っています...';

                setTimeout(() => {
                    // ランダムにキャラクターを選択して追加
                    const nameko = getRandomCharacter();
                    namekos.push(nameko);
                    message.textContent = 'なめこが成長しました！';
                    displayNamekos();
                }, growthTime);
            } else {
                message.textContent = 'なめこはもうこれ以上育ちません。';
            }
        }

        // ランダムにキャラクターを選択する
        function getRandomCharacter() {
            const randomIndex = Math.floor(Math.random() * characters.length);
            return characters[randomIndex];
        }

        // 成長機能のセットアップ
        setInterval(growNameko, growthTime + 1000);

        // なめこを表示する関数
        function displayNamekos() {
            namekoContainer.innerHTML = '<div class="log"></div>';
            const logHeight = 460;
            const totalColumns = 12;
            const totalRows = 2;
            const columnWidth = namekoContainer.offsetWidth / totalColumns;
            const rowHeight = logHeight / (totalRows + 1);

            namekos.forEach((nameko, index) => {
                const namekoElement = document.createElement('span');
                namekoElement.classList.add('nameko');

                // キャラクターの画像を表示
                const imgElement = document.createElement('img');
                imgElement.src = nameko.character_image;
                imgElement.alt = nameko.name;
                imgElement.title = nameko.name + " - " + nameko.rarity;
                imgElement.style.width = '50px';
                imgElement.style.height = '50px';

                namekoElement.appendChild(imgElement);
                namekoElement.addEventListener('click', () => harvestNameko(index));

                const positionInRow = index % totalColumns;
                const rowIndex = Math.floor(index / totalColumns);
                const xPosition = positionInRow * columnWidth;
                const yPosition = logHeight - (rowHeight * (rowIndex + 1));

                namekoElement.style.left = ${xPosition}px;
                namekoElement.style.bottom = ${yPosition}px;

                namekoContainer.appendChild(namekoElement);
            });
        }

        // なめこを収穫する関数
        function harvestNameko(index) {
            const characterId = namekos[index].id; // キャラクターのIDを取得
            // PHPにリクエストを送信してログを記録する処理を追加
            fetch('harvest.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: <?php echo $user_id; ?>,
                    character_id: characterId
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    namekos.splice(index, 1);
                    message.textContent = 'なめこを収穫しました！';
                    displayNamekos();
                } else {
                    message.textContent = '収穫の記録に失敗しました。';
                }
            })
            .catch(error => {
                console.error('エラー:', error);
                message.textContent = '収穫の記録に失敗しました。';
            });
        }
    </script>
</body>
</html>
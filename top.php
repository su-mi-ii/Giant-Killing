<?php
require 'db-connect.php';
session_start(); // セッションを開始

// ユーザーIDの取得
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo 'ログインが必要です。';
    exit;
}

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

// データベースからユーザーの所持金を取得し、セッションに保存
try {
    $sql = "SELECT money FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalMoney = $user['money'] ?? 0;
    $_SESSION['total_money'] = $totalMoney; // セッションに所持金を保存
} catch (PDOException $e) {
    echo '所持金データの取得エラー: ' . htmlspecialchars($e->getMessage());
    exit;
}

// 収穫時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $characterName = $_POST['name'] ?? '';
    $characterPoint = (int)($_POST['point'] ?? 0); // 収穫したキャラクターのポイント

    try {
        // 現在の所持金を再取得
        $sql = "SELECT money FROM users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentMoney = $user['money'] ?? 0;

        // 新しい所持金を計算
        $newMoney = $currentMoney + $characterPoint;

        // 収穫ログを保存
        $sql = "INSERT INTO harvest_log (user_id, character_id) 
                VALUES (:user_id, (SELECT character_id FROM characters WHERE name = :name LIMIT 1))";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $characterName, PDO::PARAM_STR);
        $stmt->execute();

        // 新しい所持金をデータベースで更新
        $sql = "UPDATE users SET money = :new_money WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':new_money', $newMoney, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // セッションにも新しい所持金を保存
        $_SESSION['total_money'] = $newMoney;
        echo $newMoney; // 新しい所持金を返す
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

                    /* worldbox-image自体の位置調整 */
            .worldbox-image {
                position: absolute;
                left: 20px;
                top: 80px;
                z-index: 10;
            }

            /* worldbox-image内のimg要素を円形にする */
            .worldbox-image img {
                width: 100px;
                height: 100px;
                border-radius: 50%;         /* 円形にするために50%のボーダー半径を指定 */
                border: 2px solid #fff;     /* 白い枠線を追加（必要に応じて色や太さを変更） */
                box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.5); /* 影を追加（お好みで） */
            }

            /* pointbox-image（右側の画像群）の位置調整は既存のままで問題ありません */
            .pointbox-image {
                float: right;
                position: relative;
                z-index: 10;
                margin-left: auto;
                margin-right: 30px;
                top: 80px;
            }
            .wallet-container {
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #ffcf33; /* 背景色を黄色に設定 */
                padding: 15px 25px;
                border-radius: 25px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                font-size: 1.2rem;
                font-weight: bold;
                color: #333;
                display: flex;
                align-items: center; /* テキストとアイコンを垂直方向に中央揃え */
                z-index: 10;
            }

                .wallet-container img {
                    width: 24px;
                    height: 24px;
                    margin-right: 8px;
                    vertical-align: middle; /* アイコンをテキストと中央揃え */
                }

            .shoumei {
                position: absolute;
                left: 300px;
                top: 0px; 
                z-index: 10;
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

                /* point-iconを右側中央に配置 */
        .point-icon {
            position: absolute;
            right: 20px;       /* 画面右から20pxの位置に配置 */
            top: 90%;          /* 画面の中央付近 */
            transform: translateY(-50%); /* 上下中央揃え */
            z-index: 10;
        }

    </style>
</head>
<body>
    <!-- 合計ポイント表示 -->
    <!-- 所持金表示 -->
<div class="wallet-container">
    <img src="image/coin_kinoko.png" alt="Coin Icon"> <!-- アイコンを所持金の横に表示 -->
    <span id="money-display"><?php echo htmlspecialchars($totalMoney); ?>c</span>
</div>


    <!-- 各種リンク、メインボタン、ポップアップボタン -->
    <div class="worldbox-image">
    <a href="world.php"><img src="image/world.webp" alt="世界" ></a>
    </div>

    <div class="shoumei">
    <img src="image/shoumei" alt="灯" width="150" height="200"></a>
    </div>

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

        <!-- 右側中央に配置するpointアイコン -->
    <div class="point-icon">
        <a href="saibai_item.php"><img src="image/point.webp" alt="c" width="100" height="100"></a>
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

// ページを離れる前にnamekosを保存
window.addEventListener('beforeunload', function() {
    localStorage.setItem('namekos', JSON.stringify(namekos));
});

// ページ読み込み時にnamekosを復元
window.addEventListener('load', function() {
    const savedNamekos = localStorage.getItem('namekos');
    if (savedNamekos) {
        namekos = JSON.parse(savedNamekos);
        displayNamekos();
    }
});

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
            // 修正: 所持金の表示をリアルタイムで更新
            document.getElementById('money-display').textContent = `${xhr.responseText}c`;
        } else {
            console.error('エラー: サーバーへの収穫ログ送信に失敗しました。');
        }
    };
    xhr.send(`name=${encodeURIComponent(nameko.name)}&point=${nameko.point}`);
}


    </script>
</body>
</html>

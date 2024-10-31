<?php
// ad_popup.php
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>広告ポップアップ</title>
    <style>
        #ad-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            padding: 20px;
            background-color: white;
            border: 2px solid black;
            text-align: center;
            z-index: 1000;
        }
        #ad-popup p {
            margin-bottom: 20px;
        }
        .popup-button {
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .popup-button.yes {
            background-color: lightgreen;
            border: 1px solid green;
        }
        .popup-button.no {
            background-color: lightcoral;
            border: 1px solid red;
        }
    </style>
</head>
<body>

<div id="ad-popup">
    <p>広告を閲覧すると人間が生えてきます。</p><br>
    <p>視聴しますか？</p>
    <button class="popup-button yes" onclick="redirectToAd()">はい</button>
    <button class="popup-button no" onclick="closeAdPopup()">いいえ</button>
</div>

<script>
    function redirectToAd() {
        window.location.href = 'MiyamotoOp.php';
    }

    function closeAdPopup() {
        // ポップアップを閉じる
        document.getElementById('ad-popup').style.display = 'none';
    }
</script>

</body>
</html>

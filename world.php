<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ワールド選択</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            background-color: white; 
            margin: 0; 
            padding: 0;
        }
        .container { 
            margin: 0 auto; 
            padding: 20px 50px; 
            width: 100%; 
            max-width: 600px; 
            background-color: #996633; 
            border-radius: 10px; 
            min-height: 100vh; /* ビューの高さ全体を使用 */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        .world-option { 
            display: flex; 
            align-items: center; 
            background-color: white; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 10px; 
            width: 100%; 
            box-sizing: border-box;
            text-decoration: none; /* リンクの下線を削除 */
            color: #333; /* テキストカラー */
            transition: background-color 0.3s; /* ホバー効果 */
        }
        .world-option:hover {
            background-color: #f0f0f0; /* ホバー時の背景色 */
        }
        .world-option img { 
            width: 100px; /* 画像の幅を調整 */
            height: 100px; /* 画像の高さを調整 */
            margin-right: 15px;
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
    </style>
</head>
<body>

<div class="container">
    <a href="top.php" class="back-button">← 戻る</a>
    <h1>ワールド選択</h1>

    <a href="select_world.php?world=haripota" class="world-option">
        <img src="image/haripota.png" alt="ハリポタワールド">
        <span>ハリポタワールド</span>
    </a>
    <a href="select_world.php?world=fantastic" class="world-option">
        <img src="image/fantastic.png" alt="ファンタスティックワールド">
        <span>ファンタスティックワールド</span>
    </a>
    <a href="select_world.php?world=miyamoto" class="world-option">
        <img src="image/☆１ダークサイド.png" alt="ミヤモトワールド">
        <span>☆ミヤモトワールド☆</span>
    </a>
</div>

</body>
</html>

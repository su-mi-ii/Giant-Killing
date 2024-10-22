<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダンジョン画面</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-image: url('image/danjyon_start.png'); 
            background-size: 50%;
            background-color: #000000;
            background-position: center; 
            background-repeat: no-repeat; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
            background-color: rgba(0, 0, 0, 0.6); 
            padding: 40px;
            border-radius: 15px;
        }

        .title {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
        }

        .button {
            padding: 10px 40px;
            font-size: 1.5rem;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="title">ダンジョン</div>
        
        <form action="dungeon_start.php" method="post">
            <button class="button" type="submit">スタート</button>
        </form>

        <form action="party_formation.php" method="post">
            <button class="button" type="submit">編成</button>
        </form>
    </div>

</body>
</html>

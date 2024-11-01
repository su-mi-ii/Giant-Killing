<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダメージ結果</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('result.png');
            background-size: cover;
            background-position: center;
            text-align: center;
            color: #000;
        }

        h1 {
            margin-top: 100px;
            font-size: 3rem;
            color: #000;
        }

        .damage {
            font-size: 5rem;
            color: red;
            margin: 20px 0;
        }

        .hp-status {
            font-size: 3rem;
            margin: 20px 0;
        }

        hr {
            width: 200px;
            margin: 20px auto;
            border: 2px solid #000;
        }

        .button-container {
            margin-top: 50px;
        }

        .button-container button {
            padding: 15px 30px;
            font-size: 1.5rem;
            margin: 10px;
            border: none;
            background-color: #ccc;
            border-radius: 10px;
            cursor: pointer;
        }

        .button-container button:hover {
            background-color: #bbb;
        }

    </style>
</head>
<body>

    <h1>与えたダメージ</h1>

    <div class="damage">600</div>

    <hr>

    <div class="hp-status">400/1000</div>

    <div class="button-container">
        <button>ホームに戻る</button>
        <button>ホームに戻る</button>
    </div>

</body>
</html>

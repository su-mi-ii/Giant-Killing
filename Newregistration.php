<?php
// register.php

require 'db-connect.php';

// セッションを開始
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $password = trim($_POST['password']);

    // バリデーション
    if (!empty($user_name) && !empty($password)) {
        // パスワードをハッシュ化
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // DBにユーザーを登録
            $sql = "INSERT INTO users (user_name, password) VALUES (:user_name, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            // 登録したユーザーのIDを取得
            $user_id = $pdo->lastInsertId();

            // セッションにユーザーIDを保存
            $_SESSION['user_id'] = $user_id;

            // クッキーにユーザーIDを保存（例: 30日間有効）
            setcookie('user_id', $user_id, time() + (30 * 24 * 60 * 60), '/', '', false, true);

            // 図鑑データの挿入
            $zukan_sql = "
                INSERT INTO zukan (character_id, user_id, character_image, character_description)
                VALUES
                (1, :user_id, 'image/☆１寝顔.png', '寝ることが得意。気が付いたら作業から目を背けている'),
                (2, :user_id, 'image/☆１シンプル南１.gif', '無口でシンプルな性格だが、心には深い情熱を秘めている...'),
                (3, :user_id, 'image/☆１ダークサイド.png', 'なぜ闇落ちしたのかは不明。必殺技はドラゴブラスター'),
                (4, :user_id, 'image/☆１南バズ.png', '南の地から来た冒険者。楽観的な性格で、いつも元気な声で周囲を盛り上げる'),
                (5, :user_id, 'image/☆１頭.gif', 'その頭の大きさに反して、抜群のバランス感覚を持つ。知識にあふれ、パズルや戦術的な戦いを得意とする'),
                (6, :user_id, 'image/☆２全身タイツ.png', '全身をピンクのタイツで覆い、素早い動きを得意とする。見た目のインパクトとは裏腹に、かなりのスピードを...'),
                (7, :user_id, 'image/☆２banana.gif', '常にバナナを持ち歩いており、健康志向。敵を滑らせることが得意で、トリッキーな戦い方を好む'),
                (8, :user_id, 'image/☆２ハサウェイ.gif', '宮イダーマンのライバル的存在'),
                (9, :user_id, 'image/☆２藤ンダカー.png', '呪物'),
                (10, :user_id, 'image/☆２彼女目線.png', '常に彼女目線で物事を捉える、不思議なキャラクター。敵にも思いやりを見せることがあり、その優しさが武器...'),
                (11, :user_id, 'image/☆３ちぴちゃぱ.gif', '小柄でかわいらしい外見ながら、意外とタフな性格。軽やかな身のこなしで敵を翻弄する'),
                (12, :user_id, 'image/☆３南オラフ.png', '雪の国から来た謎のキャラクター。寒さには強いが、炎には弱いという弱点がある'),
                (13, :user_id, 'image/☆４yagi.gif', 'ヤギのような強靭な精神力を持ち、どんな困難も乗り越える。角を使った突進攻撃が得意'),
                (14, :user_id, 'image/☆４宮ワトソン.png', '名探偵の助手でありながら、自身も優れた推理力を持つ。探偵スキルを駆使して敵の弱点を見つける'),
                (15, :user_id, 'image/☆４南ラッセル.png', '考古学者のような見た目で、古代の謎に興味がある。古代の力を使った攻撃が得意'),
                (16, :user_id, 'image/☆５フリー素材おじさん.png', 'どこにでもいるようなおじさんだが、実は強力な力を持つ隠れキャラ。どんな場面でも柔軟に対応する'),
                (17, :user_id, 'image/☆５宮イダーマン.png', '普段は身バレ防止のためマスクをかぶっている　正体はニューヨークに住む平凡な大学生'),
                (18, :user_id, 'image/☆５八木.png', '冷静かつ大胆な戦い方をするキャラクター。周囲の状況を見極め、最適な行動を取ることができる'),(19, :user_id, 'image/☆２南ジーニー.png', '神秘的な魔法のランプから召喚される、願いを叶える魔法使い。基本的には陽気で親切な性格だが、時には独特な方法で願いを叶えることもある。攻撃はすべて魔法を用いて行い、敵を幻惑するようなトリッキーな戦法を得意とする'),
                (20, :user_id, 'image/☆２ハートの女王らいや.png', '愛と情熱を司る女王。心を操る魔法を使い、仲間には優しく、敵には厳しい一面を持つ。彼女の魔法は、味方の回復や敵の弱体化を得意とし、特に心に響く言葉で戦局を左右する'),
                (21, :user_id, 'image/☆１内山.png', '地道な努力を重ねる勤勉なキャラクター。控えめな性格だが、困ったときには頼れる存在で、何事もコツコツと積み上げるタイプ。得意技は「根気の一撃」、一見地味だが確実に効果を発揮する堅実な攻撃を繰り出す'),
                (22, :user_id, 'image/☆３細川.png', '筋トレを愛するストイックなキャラクター。毎日欠かさずトレーニングを行い、強靭な筋肉と不屈の精神を持つ。得意技は「パワースマッシュ」、その鍛え上げられた腕力で敵を圧倒する。筋肉への情熱が周囲にも影響を与え、仲間を励ます力強い存在だ')

            ";
            $zukan_stmt = $pdo->prepare($zukan_sql);
            $zukan_stmt->bindParam(':user_id', $user_id);
            $zukan_stmt->execute();


             // itemsテーブルの挿入
            $item_sql = "
            INSERT INTO items (user_id, item_name, price, effect, item_image, level)
            VALUES
            (:user_id, '栄養剤', 5000, '成長速度上昇', 'image/eiyo.png', 0),
            (:user_id, 'レア薬', 6000, 'レアが多く生える', 'image/rea.png', 0),
            (:user_id, 'カビ治療薬', 4000, 'カビが生えなくなる', 'image/kabi.png', 0),
            (:user_id, '生命維持装置', 4000, '人間の生命を維持できる', 'image/seimei.png', 0),
            (:user_id, 'ウチヤマワールド', 4000, 'ウチヤマワールドをアンロック', 'image/☆１内山.png', 0),
            (:user_id, 'ディズニーワールド', 4000, 'ディズニーワールドをアンロック', 'image/ディズニー.png', 0),
            (:user_id, 'バナー広告消去権', 3000, 'バナー広告が表示されなくなる', 'image/koukoku.png', 0)
            ";
            $item_stmt = $pdo->prepare($item_sql);
            $item_stmt->bindParam(':user_id', $user_id);
            $item_stmt->execute();

            // toolsテーブルの挿入
            $tools_sql = "
            INSERT INTO tools (user_id, tool_name, effect, price, tool_image, level)
            VALUES
            (:user_id, '照明器', 'レア度上昇', 250, 'image/shoumei.png', 1),
            (:user_id, '加湿器', '発生速度', 200, 'image/kasitu.png', 1),
            (:user_id, '保温器', '枯れにくさ', 200, 'image/hoon.png', 1)
            ";
            $tools_stmt = $pdo->prepare($tools_sql);
            $tools_stmt->bindParam(':user_id', $user_id);
            $tools_stmt->execute();

         
            // 登録完了後 menu.php へリダイレクト
            header('Location: menu.php');
            exit;
        } catch (PDOException $e) {
            // エラーメッセージ
            $error_message = '登録中にエラーが発生しました。';
        }
    } else {
        $error_message = '全てのフィールドを入力してください。';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <style>
    /* リセットスタイル */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

h1 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

form {
    margin-bottom: 10px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #555;
}

input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

button {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 16px;
    cursor: pointer;
    margin-bottom: 10px;
}

button:hover {
    background-color: #45a049;
}

p {
    font-size: 14px;
    color: red;
    margin-bottom: 15px;
    text-align: center;
}

button[type="submit"] {
    background-color: #4CAF50;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

button[type="button"] {
    background-color: #555;
}

button[type="button"]:hover {
    background-color: #333;
}

        </style>
</head>
<body>
    <div class="container">
        <h1>新規登録</h1>
        <form method="post">
            <label for="user_name">ユーザー名</label>
            <input type="text" name="user_name" id="user_name" required>
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" required>
            <?php if (isset($error_message)) { echo '<p>' . htmlspecialchars($error_message) . '</p>'; } ?>
            <button type="submit">登録</button>
        </form>
        <form action="menu.php">
    <button type="button" onclick="window.location.href='menu.php';">戻る</button>
</form>

    </div>
</body>
</html>

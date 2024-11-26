<?php
// db-connect.php の読み込みとセッションの開始
require 'db-connect.php';
session_start();

// ログインしているユーザーのIDを取得
$user_id = $_SESSION['user_id'];

// 今日の日付を取得
$current_date = date('Y-m-d');

// データベースから今日の視聴回数を取得
$sql = "SELECT views FROM ad_views WHERE user_id = :user_id AND date = :date";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':date', $current_date, PDO::PARAM_STR);
$stmt->execute();
$ad_view = $stmt->fetch(PDO::FETCH_ASSOC);

// 今日の視聴回数を初期化
$views_today = $ad_view ? $ad_view['views'] : 0;

// 最大視聴回数を定義
$max_views_per_day = 10;

// バナー広告消去権の状態を確認
$sql = "SELECT level FROM items WHERE user_id = :user_id AND item_name = 'バナー広告消去権'";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// 広告が表示可能かを判定
$hasAdRemoval = $item && $item['level'] > 0; // 「レベルが1以上の場合は購入済み」とする

// 広告を表示可能かどうか
$canViewAd = !$hasAdRemoval && $views_today < $max_views_per_day;

// 残り視聴回数
$remainingViews = $max_views_per_day - $views_today;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>広告視聴</title>
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

<?php if ($hasAdRemoval): ?>
    <!-- 広告消去権がある場合 -->
    <div id="ad-popup">
        <p>これ以上広告を再生できません。</p>
        <button onclick="closeAdPopup()">閉じる</button>
    </div>
<?php elseif (!$canViewAd): ?>
    <!-- 今日の最大視聴回数に達した場合 -->
    <div id="ad-popup">
        <p>今日の広告視聴回数が上限に達しました。</p>
        <button onclick="closeAdPopup()">閉じる</button>
    </div>
<?php else: ?>
    <!-- 広告を表示可能な場合 -->
    <div id="ad-popup">
        <p>広告を閲覧すると人間が生えてきます。</p><br>
        <p style="color: red;">すでにヒューマンが生えていた場合<br>そこに新しいヒューマンうまれません。</p>
        <p>本日はあと <?php echo $remainingViews; ?> 回視聴できます。</p><br>
        <p>視聴しますか？</p>
        <button class="popup-button yes" onclick="redirectToAd()">はい</button>
        <button class="popup-button no" onclick="closeAdPopup()">いいえ</button>
    </div>
<?php endif; ?>

<script>
    function redirectToAd() {
        // PHPで視聴回数を記録するスクリプトへリダイレクト
        window.location.href = 'record-ad-view.php';
    }

    function closeAdPopup() {
        window.location.href = 'top.php'; // 閉じるボタンでトップに戻る
    }
</script>

</body>
</html>

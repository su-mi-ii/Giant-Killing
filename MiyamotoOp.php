<?php
$videos = ["Miya1.mp4", "Miya2.mp4"];
$randomVideo = $videos[array_rand($videos)];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>広告動画</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: black;
            overflow: hidden;
            position: relative;
            color: white;
        }

        /* 動画サイズの調整 */
        #ad-video {
            max-width: 80%;    /* 横幅を画面の80%に制限 */
            max-height: 80%;   /* 高さを画面の80%に制限 */
            object-fit: contain; /* アスペクト比を維持して表示 */
        }

        /* ×マークのスタイル */
        #close-button {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 100;
        }

        #close-button:hover {
            color: red;
        }

        #loading-message {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- 読み込み中のメッセージ -->
<div id="loading-message">読み込み中...</div>

<!-- 動画再生エリア -->
<video id="ad-video" muted playsinline preload="auto">
    <source src="MP4/<?php echo $randomVideo; ?>" type="video/mp4">
    お使いのブラウザは動画タグに対応していません。
</video>

<!-- 動画終了後に表示される閉じるボタン（×マーク） -->
<button id="close-button" onclick="closeAd()">×</button>

<script>
    const video = document.getElementById('ad-video');
    const loadingMessage = document.getElementById('loading-message');
    const closeButton = document.getElementById('close-button');

    // 動画のプリロードを完了したら再生
    video.addEventListener('canplaythrough', function() {
        loadingMessage.style.display = 'none'; // 読み込みメッセージを非表示
        video.play(); // 再生開始
    });

    // 動画が終了したら「広告を閉じる」ボタン（×）を表示
    video.onended = function() {
        closeButton.style.display = 'block';
    };

    function closeAd() {
    // メッセージをlocalStorageに保存
    localStorage.setItem('message', 'growAllNamekos');
    localStorage.setItem('debugMessage', 'デバッグ: 子ウィンドウからのメッセージ');

    // 遷移を少し遅らせる
    setTimeout(() => {
        window.location.href = 'top.php';
    }, 100); // 100ms待機
}
</script>

</body>
</html>

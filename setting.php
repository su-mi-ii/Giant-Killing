<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定画面</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #d6a85f;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .settings-container {
            margin: 50px auto;
            padding: 60px 40px;
            width: 90%;
            max-width: 800px;
            background-color: #f4f4f4;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            background-color: white;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .slider-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .slider-container label {
            margin-right: 10px;
            font-size: 18px;
        }

        input[type="range"] {
            width: 300px;
            margin-right: 10px;
        }

        #bgm-value, #se-value {
            font-size: 16px;
        }

        .bgm-selection {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
        }

        .bgm-selection hr {
            width: 100px;
        }

        .bgm-selection span {
            margin: 0 10px;
            font-size: 18px;
        }

        .bgm-options {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
        }

        .bgm-options button {
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
        }

        #bgm-title {
            font-size: 18px;
            margin: 0 20px;
        }

        .return-title {
            font-size: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="settings-container">
        <div class="back-button">
            <a href="previous_page.php">←</a>
        </div>

        <h2>サウンド</h2>

        <div class="slider-container">
            <label for="bgm-volume">BGM音量</label>
            <input type="range" id="bgm-volume" min="0" max="100" value="50">
            <span id="bgm-value">50</span>
        </div>

        <div class="slider-container">
            <label for="se-volume">SE音量</label>
            <input type="range" id="se-volume" min="0" max="100" value="50">
            <span id="se-value">50</span>
        </div>

        <div class="bgm-selection">
            <hr>
            <span>BGM</span>
            <hr>
        </div>

        <div class="bgm-options">
            <button id="bgm-prev">◀</button>
            <span id="bgm-title">朝の歌</span>
            <button id="bgm-next">▶</button>
        </div>

        <div class="return-title">
            <a href="menu.php">タイトルへ戻る</a>
        </div>
    </div>

    <audio id="bgm-audio" src="" loop></audio>
    <audio id="bgm-audio" src="" loop autoplay muted></audio>


    <script>
        const bgmAudio = document.getElementById('bgm-audio');

        const bgmVolumeSlider = document.getElementById('bgm-volume');
        const bgmValueLabel = document.getElementById('bgm-value');

        bgmAudio.volume = bgmVolumeSlider.value / 100;

        bgmVolumeSlider.addEventListener('input', function() {
            const volume = bgmVolumeSlider.value;
            bgmValueLabel.textContent = volume;
            bgmAudio.volume = volume / 100;
        });

        const bgmTitle = document.getElementById('bgm-title');
        const bgmTracks = [
            { title: '朝の歌', src: 'BGM/Morning.mp3' }, 
            { title: '別の曲', src: 'BGM/Song2.mp3' }
        ]; 
        let currentTrackIndex = 0;

        document.getElementById('bgm-prev').addEventListener('click', function() {
            currentTrackIndex = (currentTrackIndex === 0) ? bgmTracks.length - 1 : currentTrackIndex - 1;
            changeBGM();
        });

        document.getElementById('bgm-next').addEventListener('click', function() {
            currentTrackIndex = (currentTrackIndex + 1) % bgmTracks.length;
            changeBGM();
        });

        function changeBGM() {
            bgmAudio.src = bgmTracks[currentTrackIndex].src;
            bgmAudio.play();
            bgmTitle.textContent = bgmTracks[currentTrackIndex].title; 
        }

        window.onload = function() {
    bgmAudio.src = bgmTracks[0].src; 
    bgmAudio.muted = false; // 自動再生時にミュート解除
    bgmAudio.play().catch(error => {
        console.log("自動再生がブロックされました。ユーザーの操作が必要です。");
    });
    bgmTitle.textContent = bgmTracks[0].title;
};

    </script>

</body>
</html>

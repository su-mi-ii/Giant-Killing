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
            top: 40px;
            left: 30px;
        }

        .back-button a {
            display: inline-block;
            position: relative;
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: #fff;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 1rem;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            z-index: 1;
        }

        .back-button a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: skewX(-30deg);
            transition: all 0.5s ease;
            z-index: 0;
        }

        .back-button a:hover::before {
            left: 100%;
        }

        .back-button a:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
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

        #bgm-value {
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
    <div class="back-button">
        <a href="top.php">← 戻る</a>
    </div>
    <div class="settings-container">
        <h2>サウンド</h2>

        <div class="slider-container">
            <label for="bgm-volume">BGM音量</label>
            <input type="range" id="bgm-volume" min="0" max="100" value="50">
            <span id="bgm-value">50</span>
        </div>

        <div class="return-title">
            <a href="menu.php">タイトルへ戻る</a>
        </div>
    </div>

    <!-- BGMプレーヤーをiframeに分離 -->
    <iframe src="bgm_player.php" style="display:none;" id="bgm-frame"></iframe>

    <script>
        // 音量スライダーの操作
        const bgmVolumeSlider = document.getElementById('bgm-volume');
        const bgmValueLabel = document.getElementById('bgm-value');
        const bgmFrame = document.getElementById('bgm-frame').contentWindow;

        // 初期設定
        const savedVolume = localStorage.getItem('bgmVolume') || 50;
        bgmVolumeSlider.value = savedVolume;
        bgmValueLabel.textContent = savedVolume;

        bgmVolumeSlider.addEventListener('input', function () {
            const volume = bgmVolumeSlider.value;
            bgmValueLabel.textContent = volume;
            localStorage.setItem('bgmVolume', volume);
            bgmFrame.postMessage({ volume: volume / 100 }, '*');
        });

        let currentTrackIndex = 0;

        document.getElementById('bgm-prev').addEventListener('click', function () {
            currentTrackIndex = (currentTrackIndex === 0) ? bgmTracks.length - 1 : currentTrackIndex - 1;
            changeBGM();
        });

        document.getElementById('bgm-next').addEventListener('click', function () {
            currentTrackIndex = (currentTrackIndex + 1) % bgmTracks.length;
            changeBGM();
        });

        function changeBGM() {
            const track = bgmTracks[currentTrackIndex];
            bgmFrame.postMessage({ src: track.src }, '*');
            document.getElementById('bgm-title').textContent = track.title;
        }
    </script>
</body>
</html>

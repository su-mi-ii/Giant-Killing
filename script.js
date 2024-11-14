// BGM と SE の音量スライダーの値を表示
const bgmSlider = document.getElementById('bgm-volume');
const bgmValue = document.getElementById('bgm-value');
const seSlider = document.getElementById('se-volume');
const seValue = document.getElementById('se-value');

bgmSlider.addEventListener('input', function() {
    bgmValue.textContent = bgmSlider.value;
});

seSlider.addEventListener('input', function() {
    seValue.textContent = seSlider.value;
});

// BGM の選択肢を変更する処理
const bgmTitles = ["人間と一緒", "冒険の始まり", "戦いのテーマ"];
let currentBgmIndex = 0;
const bgmTitleElement = document.getElementById('bgm-title');
const prevButton = document.getElementById('bgm-prev');
const nextButton = document.getElementById('bgm-next');

prevButton.addEventListener('click', function() {
    currentBgmIndex = (currentBgmIndex > 0) ? currentBgmIndex - 1 : bgmTitles.length - 1;
    bgmTitleElement.textContent = bgmTitles[currentBgmIndex];
});

nextButton.addEventListener('click', function() {
    currentBgmIndex = (currentBgmIndex < bgmTitles.length - 1) ? currentBgmIndex + 1 : 0;
    bgmTitleElement.textContent = bgmTitles[currentBgmIndex];
});

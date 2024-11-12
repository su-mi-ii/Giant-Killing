let isVisible = false;
document.getElementById('main-button').addEventListener('click', function() {
    isVisible = !isVisible;
    togglePopups(isVisible);
});
 
function togglePopups(show) {
    const popups = document.querySelectorAll('.popup');
    popups.forEach(popup => popup.style.display = show ? 'block' : 'none');
}
 
function navigateTo(page) {
    window.location.href = page;
}
 
// PHPからキャラクター情報を取得
const characters = <?php echo json_encode($characters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
let namekos = [];
const maxNamekos = 24;
const growthTime = 5000;
 
// ページを離れる前にnamekosを保存
window.addEventListener('beforeunload', function() {
    localStorage.setItem('namekos', JSON.stringify(namekos));
});
 
// ページ読み込み時にnamekosを復元
window.addEventListener('load', function() {
    const savedNamekos = localStorage.getItem('namekos');
    if (savedNamekos) {
        namekos = JSON.parse(savedNamekos);
        displayNamekos();
    }
});
 
// なめこを成長させる関数
function growNameko() {
    if (namekos.length < maxNamekos) {
        setTimeout(() => {
            namekos.push(getRandomCharacter());
            displayNamekos();
        }, growthTime);
    }
}
 
function getRandomCharacter() {
    const probabilities = characters.map(character => 1 / character.rarity);
    const totalProbability = probabilities.reduce((sum, prob) => sum + prob, 0);
    const normalizedProbabilities = probabilities.map(prob => prob / totalProbability);
    const randomValue = Math.random();
    let cumulativeProbability = 0;
 
    for (let i = 0; i < normalizedProbabilities.length; i++) {
        cumulativeProbability += normalizedProbabilities[i];
        if (randomValue < cumulativeProbability) return characters[i];
    }
    return characters[0];
}
 
setInterval(growNameko, growthTime + 1000);
 
function displayNamekos() {
    const namekoContainer = document.getElementById('nameko-container');
    namekoContainer.innerHTML = '<div class="log"></div>';
    const containerWidth = namekoContainer.offsetWidth;
    const logHeight = window.innerHeight * 0.8;
    const totalColumns = 14;
    const offsetY = 150; // 位置を下げるオフセット（px単位）
 
    namekos.forEach((nameko, index) => {
        const namekoElement = document.createElement('span');
        const imgElement = document.createElement('img');
        imgElement.src = nameko.character_image;
        imgElement.alt = nameko.name;
        imgElement.title = `${nameko.name} - ${nameko.rarity}`;
        imgElement.style.width = '80px';
        imgElement.style.height = '80px';
        namekoElement.appendChild(imgElement);
        namekoElement.addEventListener('click', () => harvestNameko(index));
 
        const xPosition = (index % totalColumns) * (containerWidth / (totalColumns + 2));
        const yPosition = logHeight - (100 * Math.floor(index / totalColumns)) - offsetY;
        namekoElement.style.left = `${xPosition}px`;
        namekoElement.style.bottom = `${yPosition}px`;
        namekoElement.style.position = 'absolute';
        namekoContainer.appendChild(namekoElement);
    });
}
 
// なめこを収穫
function harvestNameko(index) {
    const nameko = namekos[index];
    namekos.splice(index, 1);
    displayNamekos();
 
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.querySelector('.pointbox p').textContent = `👛 ${xhr.responseText} point`;
        } else {
            console.error('エラー: サーバーへの収穫ログ送信に失敗しました');
        }
    };
    xhr.send(`name=${encodeURIComponent(nameko.name)}&point=${encodeURIComponent(nameko.point)}`);
}
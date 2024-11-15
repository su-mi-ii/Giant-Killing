<?php
// データベース接続
require 'db-connect.php';

// ログインしているユーザーIDを取得（セッションから）
session_start();
$user_id = $_SESSION['user_id'] ?? null;

// ログインしているか確認
if (!$user_id) {
    echo "ログインしてください。";
    exit;
}

// キャラクター一覧を取得
try {
    $stmt = $pdo->prepare("
        SELECT c.character_id, c.name, c.rarity, c.attack_type, c.attack_count, c.character_image, c.point, h.harvest_time
        FROM characters AS c
        JOIN harvest_log AS h ON c.character_id = h.character_id
        WHERE h.user_id = :user_id
        ORDER BY h.harvest_time DESC
        LIMIT 30
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "データ取得エラー: " . $e->getMessage();
    exit;
}

// パーティー配列をセッションで管理（character_id と harvest_time を使用）
if (!isset($_SESSION['party'])) {
    $_SESSION['party'] = [1 => null, 2 => null, 3 => null];
}
$party = &$_SESSION['party'];

// キャラクターをパーティーに追加（重複する character_id と harvest_time の組み合わせを許可しない）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_party'])) {
    $character_id = $_POST['character_id'];
    $harvest_time = $_POST['harvest_time']; // 収穫時間も一緒に送信

    // 同じ character_id と harvest_time の組み合わせがない場合のみ追加
    $is_duplicate = false;
    foreach ($party as $existing_character) {
        if ($existing_character && $existing_character['character_id'] == $character_id && $existing_character['harvest_time'] == $harvest_time) {
            $is_duplicate = true;
            break;
        }
    }

    if (!$is_duplicate) {
        foreach ($party as $position => $existing_character) {
            if ($existing_character === null) {
                $party[$position] = ['character_id' => $character_id, 'harvest_time' => $harvest_time];
                break;
            }
        }
    }
}

// キャラクターをパーティーから取り消し
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_party'])) {
    $character_id = $_POST['character_id'];
    $harvest_time = $_POST['harvest_time'];

    foreach ($party as $position => $existing_character) {
        if ($existing_character && $existing_character['character_id'] == $character_id && $existing_character['harvest_time'] == $harvest_time) {
            $party[$position] = null;
            break;
        }
    }
}

// パーティーを保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_party'])) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM party WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        foreach ($party as $position => $character) {
            if ($character !== null) {
                // positionフィールドをparty_positionに変更
                $stmt = $pdo->prepare("INSERT INTO party (user_id, character_id, position) VALUES (:user_id, :character_id, :position)");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':character_id', $character['character_id'], PDO::PARAM_INT);
                $stmt->bindParam(':position', $position, PDO::PARAM_INT);  // 修正箇所
                //$stmt->bindParam(':harvest_time', $character['harvest_time']);
                $stmt->execute();
            }
        }
        $pdo->commit();
        echo "パーティーが保存されました。";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "保存エラー: " . $e->getMessage();
    }
}

// キャラクター詳細の表示
$selected_character = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_character'])) {
    $selected_character_id = $_POST['character_id'];
    $selected_harvest_time = $_POST['harvest_time'];
    foreach ($characters as $character) {
        if ($character['character_id'] == $selected_character_id && $character['harvest_time'] == $selected_harvest_time) {
            $selected_character = $character;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>編成画面</title>
    <style>
        .container { display: flex; }
        .character-list { width: 70%; display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
        .character { border: 2px solid blue; padding: 10px; text-align: center; cursor: pointer; }
        
        /* 画像の大きさを枠に合わせる */
        .character img, .slot img {
            width: 100%;
            height: auto;
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .details { width: 30%; border: 1px solid #000; padding: 10px; margin-left: 10px; }
        .party-slot-container { display: flex; gap: 10px; margin-top: 20px; justify-content: center; }
        .slot { border: 2px dashed gray; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .controls { margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h1>編成画面</h1>

    <!-- パーティースロット -->
    <div class="party-slot-container">
        <?php foreach ($party as $position => $character): ?>
            <div class="slot" onclick="selectCharacter(<?= htmlspecialchars(json_encode($character), ENT_QUOTES, 'UTF-8') ?>)">
                <?= $character ? '<img src="' . htmlspecialchars($characters[array_search($character['character_id'], array_column($characters, 'character_id'))]['character_image'], ENT_QUOTES, 'UTF-8') . '" alt="キャラ">' : $position . '体目' ?>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" class="controls">
        <button type="submit" name="save_party">保存</button>
        <button type="button" onclick="window.location.href='start.php'">戻る</button>
    </form>

    <!-- キャラクターリストと詳細表示 -->
    <div class="container">
        <div class="character-list">
            <?php foreach ($characters as $character): ?>
                <form class="character" method="POST">
                    <input type="hidden" name="character_id" value="<?= htmlspecialchars($character['character_id'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="harvest_time" value="<?= htmlspecialchars($character['harvest_time'], ENT_QUOTES, 'UTF-8') ?>">
                    <img src="<?= htmlspecialchars($character['character_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8') ?>" onclick="selectCharacter(<?= htmlspecialchars(json_encode($character), ENT_QUOTES, 'UTF-8') ?>)">
                    <p><?= htmlspecialchars($character['name'], ENT_QUOTES, 'UTF-8') ?></p>

                    <!-- 編成済みキャラのボタンを「取り消し」に変更 -->
                    <?php
                    $is_in_party = false;
                    foreach ($party as $party_character) {
                        if ($party_character && $party_character['character_id'] == $character['character_id'] && $party_character['harvest_time'] == $character['harvest_time']) {
                            $is_in_party = true;
                            break;
                        }
                    }
                    ?>
                    <?php if ($is_in_party): ?>
                        <button type="submit" name="remove_from_party">取り消し</button>
                    <?php else: ?>
                        <button type="submit" name="add_to_party">編成</button>
                    <?php endif; ?>
                </form>
            <?php endforeach; ?>
        </div>

        <!-- 選択したキャラクターの詳細 -->
        <div class="details">
            <h2>選択したキャラクターの詳細</h2>
            <?php if ($selected_character): ?>
                <p>名前: <?= htmlspecialchars($selected_character['name'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>レア度: <?= htmlspecialchars($selected_character['rarity'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>攻撃タイプ: <?= htmlspecialchars($selected_character['attack_type'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>攻撃回数: <?= htmlspecialchars($selected_character['attack_count'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>ポイント: <?= htmlspecialchars($selected_character['point'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p>キャラクターを選択してください。</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function selectCharacter(character) {
            const form = document.createElement('form');
            form.method = 'POST';
            const characterIdInput = document.createElement('input');
            characterIdInput.type = 'hidden';
            characterIdInput.name = 'character_id';
            characterIdInput.value = character.character_id;
            form.appendChild(characterIdInput);

            const harvestTimeInput = document.createElement('input');
            harvestTimeInput.type = 'hidden';
            harvestTimeInput.name = 'harvest_time';
            harvestTimeInput.value = character.harvest_time;
            form.appendChild(harvestTimeInput);

            const submit = document.createElement('input');
            submit.type = 'hidden';
            submit.name = 'select_character';
            form.appendChild(submit);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>

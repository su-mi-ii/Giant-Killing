<?php
session_start();
require 'db-connect.php';

// 戦闘データを取得
$battle_data = &$_SESSION['battle_data'];

// ターン数初期化（初回アクセス時）
if (!isset($battle_data['turn_count'])) {
    $battle_data['turn_count'] = 1;
}

// 現在の先頭キャラクターを取得
$player_front = &$battle_data['player_team'][$battle_data['player_front']];
$enemy_front = &$battle_data['enemy_team'][$battle_data['enemy_front']];

// プレイヤーの行動処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $battle_data['logs'][] = "=== ターン {$battle_data['turn_count']} ===";

    // 通常攻撃
    if ($_POST['action'] === 'attack') {
        handleAttack($player_front, $enemy_front, $battle_data);
    }

    // スキル使用
    elseif ($_POST['action'] === 'skill' && isset($_POST['skill_id'])) {
        $skill_id = (int)$_POST['skill_id'];
        $selected_skill = findSkillById($player_front['skills'], $skill_id);
        if ($selected_skill) {
            handleSkill($player_front, $enemy_front, $selected_skill, $battle_data);
            // 敵が生存している場合に反撃
            if ($enemy_front['HP'] > 0) {
                enemyAttack($player_front, $enemy_front, $battle_data);
            }
        }
    }

    // バトル進行チェック
    checkBattleStatus($battle_data);

    // ターン終了後に次のターンに移行
    $battle_data['turn_count']++;
    $_SESSION['battle_data'] = $battle_data;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// スキル検索関数
function findSkillById($skills, $skill_id) {
    foreach ($skills as $skill) {
        if ($skill['skill_id'] === $skill_id) {
            return $skill;
        }
    }
    return null;
}

// 通常攻撃処理
function handleAttack(&$attacker, &$defender, &$battle_data) {
    if ($attacker['speed'] >= $defender['speed']) {
        $battle_data['logs'][] = "{$attacker['name']} のスピードが速いため、先攻！";
        performAttack($attacker, $defender, $battle_data);
        if ($defender['HP'] > 0) {
            enemyAttack($attacker, $defender, $battle_data);
        }
    } else {
        $battle_data['logs'][] = "{$defender['name']} のスピードが速いため、先攻！";
        enemyAttack($attacker, $defender, $battle_data);
        if ($attacker['HP'] > 0) {
            performAttack($attacker, $defender, $battle_data);
        }
    }
}

// スキル処理関数
function handleSkill(&$user, &$target, $skill, &$battle_data) {
    $battle_data['logs'][] = "{$user['name']} は特技「{$skill['name']}」を使用した！";

    switch ($skill['type']) {
        case 'speed':
            if ($skill['name'] === '影走り') {
                $user['evade_next'] = true; // 次ターンの回避フラグ
                $battle_data['logs'][] = "{$user['name']} は影走りで次の攻撃を回避する準備をした！";
            } elseif ($skill['name'] === '先制攻撃') {
                if ($target['speed'] > $user['speed']) {
                    $user['speed'] = $target['speed'] + 1; // 一時的に速度を上げる
                    $battle_data['logs'][] = "{$user['name']} は次のターンで必ず先制攻撃を行う準備をした！";
                }
                performAttack($user, $target, $battle_data); // 即座に通常攻撃
            } elseif ($skill['name'] === 'すり抜け') {
                $battle_data['logs'][] = "{$user['name']} は防御を無視する攻撃を仕掛けた！";
                performAttack($user, $target, $battle_data);
            }
            break;
        case 'attack':
            $damage = $skill['power'];
            $target['HP'] = max(0, $target['HP'] - $damage);
            $battle_data['logs'][] = "{$target['name']} に {$damage} のダメージ！";

            // 敵が倒れた場合の処理
            if ($target['HP'] <= 0) {
                $battle_data['logs'][] = "{$target['name']} は倒れた！";
                if ($target === $battle_data['enemy_team'][$battle_data['enemy_front']]) {
                    $battle_data['enemy_front']++;
                }
            }
            break;

        case 'heal':
        case 'hp':
            if ($skill['name'] === '自己修復') {
                $heal = min($skill['power'], $user['HP_max'] - $user['HP']);
                $user['HP'] += $heal;
                $battle_data['logs'][] = "{$user['name']} は自己修復で {$heal} HP を回復した！";
            } elseif ($skill['name'] === '鉄壁の守り') {
                $user['defense_boost'] = 2; // 次の2ターン間ダメージ半減
                $battle_data['logs'][] = "{$user['name']} は鉄壁の守りで受けるダメージを半減する！";
            } elseif ($skill['name'] === '反撃') {
                $user['counter_chance'] = 0.6; // 反撃率を60%に設定
                $battle_data['logs'][] = "{$user['name']} は反撃態勢に入った！";
            }
            break;
    }
}

// 攻撃実行処理
function performAttack(&$attacker, &$defender, &$battle_data) {
    $damage = rand(10, 20);

    // ダメージ軽減処理
    if (isset($defender['defense_boost']) && $defender['defense_boost'] > 0) {
        $damage = floor($damage / 2);
        $battle_data['logs'][] = "{$defender['name']} は鉄壁の守りでダメージが半減した！";
    }

    $defender['HP'] = max(0, $defender['HP'] - $damage);
    $battle_data['logs'][] = "{$attacker['name']} の攻撃！ {$defender['name']} に {$damage} ダメージ！";

    // 鉄壁の守りターン数減少
    if (isset($defender['defense_boost']) && $defender['defense_boost'] > 0) {
        $defender['defense_boost']--;
    }

    if ($defender['HP'] <= 0) {
        $battle_data['logs'][] = "{$defender['name']} は倒れた！";
        if ($defender === $battle_data['enemy_team'][$battle_data['enemy_front']]) {
            $battle_data['enemy_front']++;
        }
    }
}
// 敵の攻撃処理 (修正版)
function enemyAttack(&$player_front, &$enemy_front, &$battle_data) {
    // スキル使用または通常攻撃をランダムで選択
    $use_skill = rand(0, 1) === 1; // 50%の確率でスキルを使用

    if ($use_skill && !empty($enemy_front['skills'])) {
        // 敵のスキルをランダムで選択
        $skill = $enemy_front['skills'][array_rand($enemy_front['skills'])];

        // 必中スキルの判定
        $is_certain_hit = isset($skill['certain_hit']) && $skill['certain_hit'] === true;

        // スキルの成功率をチェック
        if (rand(1, 100) <= $skill['success_rate']) {
            $battle_data['logs'][] = "{$enemy_front['name']} は特技「{$skill['name']}」を使用した！";

            // 必中スキルの場合、回避を無視
            if (!$is_certain_hit && isset($player_front['evade_next']) && $player_front['evade_next'] === true) {
                $battle_data['logs'][] = "{$player_front['name']} は影走りで攻撃を回避した！";
                $player_front['evade_next'] = false; // 回避後にリセット
                return; // 攻撃終了
            }

            switch ($skill['type']) {
                case 'attack':
                    $damage = $skill['power'];
                    $player_front['HP'] = max(0, $player_front['HP'] - $damage);
                    $battle_data['logs'][] = "{$player_front['name']} に {$damage} ダメージ！";

                    // プレイヤーが倒れた場合の処理
                    if ($player_front['HP'] <= 0) {
                        $battle_data['logs'][] = "{$player_front['name']} は倒れた！";
                        $battle_data['player_front']++; // 次のキャラクターに切り替え
                        checkBattleStatus($battle_data); // バトル終了条件をチェック
                        return; // 処理を終了
                    }
                    break;

                case 'heal':
                    $heal = min($skill['power'], $enemy_front['HP_max'] - $enemy_front['HP']);
                    $enemy_front['HP'] += $heal;
                    $battle_data['logs'][] = "{$enemy_front['name']} は {$heal} HP を回復した！";
                    break;

                case 'speed':
                    // 速度アップなどの特殊効果
                    $enemy_front['speed'] += $skill['power'];
                    $battle_data['logs'][] = "{$enemy_front['name']} はスピードが {$skill['power']} 上昇した！";
                    break;
            }
        } else {
            $battle_data['logs'][] = "{$enemy_front['name']} の特技「{$skill['name']}」は失敗した！";
        }
        return; // スキルを使用した場合は終了
    }

    // 通常攻撃処理
    $damage = rand(10, 20);

    // 必中攻撃の判定
    if (!isset($enemy_front['certain_hit']) && isset($player_front['evade_next']) && $player_front['evade_next'] === true) {
        $battle_data['logs'][] = "{$player_front['name']} は影走りで攻撃を回避した！";
        $player_front['evade_next'] = false; // 回避後にリセット
        return; // 攻撃終了
    }

    // ダメージ軽減処理 (鉄壁の守り)
    if (isset($player_front['defense_boost']) && $player_front['defense_boost'] > 0) {
        $damage = floor($damage / 2);
        $battle_data['logs'][] = "{$player_front['name']} は鉄壁の守りでダメージが半減した！";
    }

    // プレイヤーにダメージ適用
    $player_front['HP'] = max(0, $player_front['HP'] - $damage);
    $battle_data['logs'][] = "{$enemy_front['name']} の攻撃！ {$player_front['name']} に {$damage} ダメージ！";

    // プレイヤーが倒れた場合
    if ($player_front['HP'] <= 0) {
        $battle_data['logs'][] = "{$player_front['name']} は倒れた！";
        $battle_data['player_front']++; // 次のキャラクターに切り替え
        checkBattleStatus($battle_data); // バトル終了条件をチェック
        return; // 処理を終了
    }

    // 反撃処理
    if (isset($player_front['counter_chance']) && rand(1, 100) <= $player_front['counter_chance'] * 100) {
        $counter_damage = floor($damage * 0.6); // ダメージの60%を返す
        $enemy_front['HP'] = max(0, $enemy_front['HP'] - $counter_damage); // 敵にダメージを適用
        $battle_data['logs'][] = "{$player_front['name']} の反撃！ {$enemy_front['name']} に {$counter_damage} ダメージ！";

        // 敵が倒れた場合の処理
        if ($enemy_front['HP'] <= 0) {
            $battle_data['logs'][] = "{$enemy_front['name']} は倒れた！";
            $battle_data['enemy_front']++;
        }
    }
}



// バトル終了条件の確認
function checkBattleStatus(&$battle_data) {
    if ($battle_data['enemy_front'] >= count($battle_data['enemy_team'])) {
        header("Location: result.php?result=win");
        exit;
    } elseif ($battle_data['player_front'] >= count($battle_data['player_team'])) {
        header("Location: result.php?result=lose");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>バトル画面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #2c3e50, #34495e);
            color: #ecf0f1;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #f1c40f;
            text-align: center;
            margin: 20px 0;
            text-shadow: 2px 2px 4px #000;
        }
        .battle-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        .character-box {
            text-align: center;
            padding: 10px;
        }
        .character-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 2px solid #ecf0f1;
        }
        .character-box p {
            margin: 5px 0;
            font-size: 16px;
        }
        .log-box {
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            max-width: 90%;
            height: 200px;
            overflow-y: auto;
            box-shadow: inset 0 4px 10px rgba(0, 0, 0, 0.5);
        }
        .form-container {
            margin: 20px auto;
            padding: 10px;
            max-width: 500px;
            text-align: center;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        .skill-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        button {
            padding: 10px 20px;
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: background 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background: #c0392b;
            transform: scale(1.05);
        }
        .skill-details {
            font-size: 14px;
            color: #bdc3c7;
        }
    </style>
</head>
<body>
<iframe src="btlbgm_player.php" style="display:none;" id="bgm-frame"></iframe>
<?php
// 画像パスを生成する関数（日本語ファイル名対応）
function getCharacterImagePath($name) {
    $possible_extensions = ['png', 'jpg', 'jpeg', 'gif'];
    foreach ($possible_extensions as $ext) {
        // サーバー上の実際のパス
        $path = "image/{$name}.{$ext}";
        if (file_exists($path)) {
            // 日本語ファイル名をエンコードしてURLとして安全に出力
            return htmlspecialchars(urlencode($path));
        }
    }
    return "image/default.png"; // デフォルト画像
}
?>

    <h1>バトル画面 - ターン <?= htmlspecialchars($battle_data['turn_count']) ?></h1>
    <div class="battle-container">
        <div class="character-box">
        <img src="<?= htmlspecialchars($player_front['character_image'] ?? 'default_player.png') ?>" alt="<?= htmlspecialchars($player_front['name']) ?>">
            <h2>プレイヤー</h2>
            <p><strong><?= htmlspecialchars($player_front['name']) ?></strong></p>
            <p>HP: <strong><?= $player_front['HP'] ?>/<?= $player_front['HP_max'] ?></strong></p>
            <p>スピード: <strong><?= $player_front['speed'] ?></strong></p>
        </div>
        <div class="character-box">
        <img src="<?= htmlspecialchars($enemy_front['character_image'] ?? 'default_enemy.png') ?>" alt="<?= htmlspecialchars($enemy_front['name']) ?>">
            <h2>敵</h2>
            <p><strong><?= htmlspecialchars($enemy_front['name']) ?></strong></p>
            <p>HP: <strong><?= $enemy_front['HP'] ?>/<?= $enemy_front['HP_max'] ?></strong></p>
            <p>スピード: <strong><?= $enemy_front['speed'] ?></strong></p>
        </div>
    </div>

    <div class="form-container">
        <form method="POST">
            <div class="button-group">
                <button name="action" value="attack">通常攻撃</button>
            </div>
            <div class="skill-buttons">
                <?php foreach ($player_front['skills'] as $skill): ?>
                    <button name="action" value="skill" onclick="this.form.skill_id.value='<?= htmlspecialchars($skill['skill_id']) ?>';">
                        <?= htmlspecialchars($skill['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="skill_id">
        </form>
    </div>

    <div class="log-box">
        <?php foreach (array_reverse($battle_data['logs']) as $log): ?>
            <p><?= htmlspecialchars($log) ?></p>
        <?php endforeach; ?>
    </div>
    
</body>
</html>



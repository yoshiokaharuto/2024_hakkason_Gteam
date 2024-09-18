<?php
    require_once "./db_connect.php";

    if(isset($_GET['id'])) {
        $sql = "SELECT 1 FROM recipes where recipe_id = :id LIMIT 1";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
        $stm->execute();
        if($stm->fetchColumn() !== false) {
            $sql = "
                SELECT r.*, 
                GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name ASC SEPARATOR ', ') AS category, 
                GROUP_CONCAT(DISTINCT mi.ingredient_name ORDER BY mi.ingredient_name ASC SEPARATOR ', ') AS main_ingredient
                FROM recipes r
                LEFT JOIN recipe_to_category rtc ON r.recipe_id = rtc.recipe_id
                LEFT JOIN categories c ON rtc.category_id = c.category_id
                LEFT JOIN recipe_to_ingredient rti ON r.recipe_id = rti.recipe_id
                LEFT JOIN main_ingredients mi ON rti.ingredient_id = mi.ingredient_id
                WHERE r.recipe_id = :id
                GROUP BY r.recipe_id
                ORDER BY r.recipe_id DESC
            ";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
            $stm->execute();
            $data = $stm->fetch(PDO::FETCH_ASSOC);
        } else {
            header("Location: index.php");
        }
    } else {
        header("Location: index.php");
    }

    function preoutput($str) {
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        return nl2br($str);
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一覧・検索画面</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
<header>
        <a href="index.php">
            <h1 class="app-name">
                アプリ名
            </h1>
        </a>
        <div id="header-icon-container">
            <a href="post.php" title="新規投稿">
                <span class="material-symbols-outlined">add_circle</span>
            </a>
            <a href="settings.php" title="設定">
                <span class="material-symbols-outlined">settings</span>
            </a>
            <a href="login.php" title="ログアウト"> <!-- 仮でログイン画面に飛びます -->
                <span class="material-symbols-outlined">logout</span>
            </a>
        </div>
        <label id="sub-header-button-container">
            <input type="checkbox" id="sub-header-checkbox">
            <span class="material-symbols-outlined" id="sub-header-button">
                menu
            </span>
        </label>
    </header>

    <div id="sub-header">
        <ul>
            <a href="post.php" title="新規投稿">
                <li>新規投稿</li>
            </a>
            <a href="settings.php" title="設定">
                <li>設定</li>
            </a>
            <a href="login.php" title="ログアウト"> <!-- 仮でログイン画面に飛びます -->
                <li>ログアウト</li>
            </a>
        </ul>
    </div>

    <main>
        <div class="recipe-card">
            <div class="recipe-name-section">
                <h1 class="recipe-name">
                    <?= preoutput($data['name']) ?>
                </h1>
                <p class="recipe-genre">
                    <?php
                        switch($data["genre"]) {
                            case 0:
                                echo "和";
                                break;
                            case 1:
                                echo "洋";
                                break;
                            case 2:
                                echo "中";
                                break;
                            case 3:
                                echo "デ";
                                break;
                            default:
                                echo $data['genre'];
                                break;
                        }
                    ?>
                </p>
            </div>
            <p class="recipe-time">
                <span class="material-symbols-outlined">
                    timer
                </span>
                <?= preoutput($data['time']) ?>分
            </p>
            <?php
                if(!empty($data['category'])) {
                    echo "<p class='recipe-category'><span>".str_replace(', ', '</span><span>', $data['category'])."</span></p>";
                }
            ?>
            <?php
                if(!empty($data['main_ingredient'])) {
                    echo "<p class='recipe-mainIngredient'><span>".str_replace(', ', '</span><span>', $data['main_ingredient'])."</span></p>";
                }
            ?>
            <p class="recipe-date">
                <?= preoutput($data['date']) ?>
            </p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>
                    <span class="material-symbols-outlined">
                        grocery
                    </span>
                    食材
                </p>
                <button onclick="copyButton('recipe-ingredient')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-ingredient">
                <?= preoutput($data['ingredient']) ?>
            </p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>
                    <span class="material-symbols-outlined">
                        receipt_long
                    </span>
                    手順
                </p>
                <button onclick="copyButton('recipe-process')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-process">
                <?= preoutput($data['process']) ?>
            </p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>
                    <span class="material-symbols-outlined">
                        description
                    </span>
                    メモ
                </p>
            <?php if(!empty($data['note'])) { ?>
                    <button onclick="copyButton('recipe-note')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-note">
                <?= preoutput($data['note']) ?>
            </p>
            <?php } else { ?>
                </div>
                <p class="recipe-information-content" id="recipe-note">(なし)</p>
            <?php } ?>
        </div>
        <div id="recipe-dataToCopy">【レシピ名】<?= preoutput($data['name']) ?><br>
【ジャンル】<?php
                switch($data["genre"]) {
                    case 0:
                        echo "和風";
                        break;
                    case 1:
                        echo "洋風";
                        break;
                    case 2:
                        echo "中華風";
                        break;
                    case 3:
                        echo "お菓子・デザート";
                        break;
                    default:
                        echo $data['genre'];
                        break;
                }
            ?><br>
【所要時間】<?= preoutput($data['time']) ?>分<br>
【食材】<br>
<?= preoutput($data['ingredient']) ?><br>
【主要食材】<?= !empty($data['main_ingredient']) ? preoutput($data['main_ingredient']) : "(なし)" ?><br>
【手順】<br>
<?= preoutput($data['process']) ?><br>
【メモ】<br>
<?= !empty($data['note']) ? preoutput($data['note']) : "(なし)" ?><br>
【カテゴリタグ】<?= !empty($data['category']) ? preoutput($data['category']) : "(なし)" ?></div>

        <div class="button-container">
            <a href='edit.php?id=<?= $data['recipe_id'] ?>' class="main-button">
                <span class="material-symbols-outlined">
                    edit
                </span>
                編集する
            </a>
            <a href='delete.php?id=<?= $data['recipe_id'] ?>' class="main-button">
                <span class="material-symbols-outlined">
                    delete
                </span>
                削除する
            </a>
        </div>

        <div class="button-container">
            <a href="index.php" class="white-button">
                <span class="material-symbols-outlined">
                    undo
                </span>
                レシピ一覧に戻る
            </a>
            <button onclick="copyAllButton()" class="main-button">
                <span class="material-symbols-outlined">
                    content_copy
                </span>
                このレシピをコピー
            </button>
        </div>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
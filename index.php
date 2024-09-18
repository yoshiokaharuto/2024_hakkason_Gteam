<?php
require_once "./db_connect.php";

// 検索条件の初期化
$recipe_name = isset($_GET['recipe_name']) && $_GET['recipe_name'] !== '' ? '%' . $_GET['recipe_name'] . '%' : '%';
$category_tag = isset($_GET['category_tag']) && $_GET['category_tag'] !== '' ? $_GET['category_tag'] : null;
$ingredient_tag = isset($_GET['ingredient_tag']) && $_GET['ingredient_tag'] !== '' ? $_GET['ingredient_tag'] : null;

// カテゴリタグの取得
$sql_categories = "SELECT category_name FROM categories";
$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute();

//データベースからカテゴリタグを取得して$categoriesに格納
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// 主要食材タグの取得
$sql_ingredients = "SELECT ingredient_name FROM main_ingredients";
$stmt_ingredients = $pdo->prepare($sql_ingredients);
$stmt_ingredients->execute();

//データベースから主要食材タグを取得してに$ingredients格納
$ingredients = $stmt_ingredients->fetchAll(PDO::FETCH_ASSOC);

// SQLクエリ作成（カテゴリや食材がNULLの場合の扱いを修正）
$sql = "
    SELECT r.*, 
    GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name ASC SEPARATOR '</span><span>') AS category, 
    GROUP_CONCAT(DISTINCT mi.ingredient_name ORDER BY mi.ingredient_name ASC SEPARATOR '</span><span>') AS main_ingredient
    FROM recipes r
    LEFT JOIN recipe_to_category rtc ON r.recipe_id = rtc.recipe_id
    LEFT JOIN categories c ON rtc.category_id = c.category_id
    LEFT JOIN recipe_to_ingredient rti ON r.recipe_id = rti.recipe_id
    LEFT JOIN main_ingredients mi ON rti.ingredient_id = mi.ingredient_id
    WHERE r.name LIKE :recipe_name
    AND (:category_tag IS NULL OR c.category_name = :category_tag)
    AND (:ingredient_tag IS NULL OR mi.ingredient_name = :ingredient_tag)
    GROUP BY r.recipe_id
    ORDER BY r.recipe_id DESC
";

// SQLを準備してパラメータをバインド
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':recipe_name', $recipe_name, PDO::PARAM_STR);
$stmt->bindValue(':category_tag', $category_tag, PDO::PARAM_STR);
$stmt->bindValue(':ingredient_tag', $ingredient_tag, PDO::PARAM_STR);

// クエリを実行
$stmt->execute();

// 結果を取得
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// ランダムなページに飛ぶボタンのための処理
$sql = "SELECT MAX(recipe_id) FROM recipes";
$stmt = $pdo->query($sql);
$maxId = $stmt->fetchColumn();
if($maxId !== false) {
    do {
        $randomPageId = rand(1, $maxId);
        $sql = "SELECT 1 FROM recipes where recipe_id = :id LIMIT 1";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':id', $randomPageId, PDO::PARAM_INT);
        $stm->execute();
    } while ($stm->fetchColumn() === false);
}

session_start(); //セッション開始
$resultMessage = '';
if(isset($_SESSION['resultMessage'])) {
    $resultMessage = $_SESSION['resultMessage'];
    unset($_SESSION['resultMessage']);//メッセージ表示後に削除
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
        <form action="index.php" method="GET">
            <div id="search-container">
                <input type="text" name="recipe_name" value="<?= htmlspecialchars($_GET['recipe_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="検索したいレシピ名を入力…">
                <div id="searchByTag-container">
                    <!-- カテゴリタグの<select> -->
                    <!-- 取得したカテゴリタグと主要食材タグをそれぞれ<option>として動的に生成 -->
                    <select name="category_tag" class="post-item">
                        <option value="">カテゴリを選択</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($_GET['category_tag']) && $_GET['category_tag'] === $category['category_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- 主要食材タグの<select> -->
                    <select name="ingredient_tag" class="post-item">
                        <option value="">主要食材を選択</option>
                        <?php foreach($ingredients as $ingredient): ?>
                            <option value="<?= htmlspecialchars($ingredient['ingredient_name'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($_GET['ingredient_tag']) && $_GET['ingredient_tag'] === $ingredient['ingredient_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ingredient['ingredient_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label id="user-recipe">
                    <input type="checkbox" name="user-recipe">自分が投稿したレシピ</input>
                </label>
                <div id="index-button-container">
                    <button type="submit" id="search-button">
                        <span class="material-symbols-outlined">
                            search
                        </span>
                        検索
                    </button>
                    <a href="index.php">
                        <span class="material-symbols-outlined">
                            restart_alt
                        </span>
                        検索条件をリセット
                    </a>
                    <a href='detail.php?id=<?= $randomPageId ?>'>
                        <span class="material-symbols-outlined">
                            shuffle
                        </span>
                        ランダム
                    </a>
                </div>
            </div>
        </form>
        <?php
        if ($results) {
            foreach($results as $data){
        ?>
            <a href='detail.php?id=<?= $data['recipe_id'] ?>'>
                <div class='recipe-card'>
                    <div class='recipe-name-section'>
                        <h1 class='recipe-name'><?= htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                        <p class='recipe-genre'>
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
                    <p class='recipe-time'>
                        <span class='material-symbols-outlined'>timer</span>
                        <?= htmlspecialchars($data['time'], ENT_QUOTES, 'UTF-8') . "分"; ?>
                    </p>
                    <?php
                        if(!empty($data['category'])) {
                            echo "<p class='recipe-category'><span>".$data['category']."</span></p>";
                        }
                    ?>
                    <?php
                        if(!empty($data['main_ingredient'])) {
                            echo "<p class='recipe-mainIngredient'><span>".$data['main_ingredient']."</span></p>";
                        }
                    ?>
                    <p class='recipe-ingredient'><?= nl2br(htmlspecialchars($data['ingredient'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <p class='recipe-userAndDate'>
                            <?= htmlspecialchars($data['user_id'], ENT_QUOTES, 'UTF-8') ?>(<?= htmlspecialchars($data['date'], ENT_QUOTES, 'UTF-8') ?>)
                        </p>
                </div>
            </a>
        <?php 
            }
        } else {
            echo "<p id='not-found'>レシピが見つかりませんでした。</p>";
        }
        ?>

        <?php if (!empty($resultMessage)): ?>
            <script>
                alert('<?php echo $resultMessage; ?>');
            </script>
        <?php endif; ?>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
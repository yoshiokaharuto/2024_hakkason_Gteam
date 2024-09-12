<?php
require_once "./db_connect.php";

// 検索条件の初期化
$recipe_name = isset($_GET['recipe_name']) && $_GET['recipe_name'] !== '' ? '%' . $_GET['recipe_name'] . '%' : '%';
$category_tag = isset($_GET['category_tag']) && $_GET['category_tag'] !== '' ? $_GET['category_tag'] : null;
$ingredient_tag = isset($_GET['ingredient_tag']) && $_GET['ingredient_tag'] !== '' ? $_GET['ingredient_tag'] : null;

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
        <h1 class="app-name">アプリ名</h1>
        <a href="post.php">
            <span class="material-symbols-outlined">
                add_circle
            </span>
        </a>
    </header>

    <main>
        <form action="index.php" method="GET">
            <div id="searchByName-container">
                <input type="text" name="recipe_name" value="<?= htmlspecialchars($_GET['recipe_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="検索したいレシピ名を入力…">
                <div>
                    <input type="text" name="category_tag" value="<?= htmlspecialchars($_GET['category_tag'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="カテゴリタグ(後でselectにする)">
                    <input type="text" name="ingredient_tag" value="<?= htmlspecialchars($_GET['ingredient_tag'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="主要食材タグ(後でselectにする)">
                </div>

                <button type="submit" id="search-button">
                    検索
                    <span class="material-symbols-outlined">
                        search
                    </span>
                </button>
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
                    <p class='recipe-date'><?= htmlspecialchars($data['date'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </a>
        <?php 
            }
        } else {
            echo "<p id='not-found'>レシピが見つかりませんでした。</p>";
        }
        ?>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>
</body>
</html>
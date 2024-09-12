<?php
require_once 'db_search_demo.php';

// 検索条件の初期化
$recipe_name = isset($_GET['recipe_name']) && $_GET['recipe_name'] !== '' ? '%' . $_GET['recipe_name'] . '%' : '%';
$category_tag = isset($_GET['category_tag']) && $_GET['category_tag'] !== '' ? $_GET['category_tag'] : null;
$ingredient_tag = isset($_GET['ingredient_tag']) && $_GET['ingredient_tag'] !== '' ? $_GET['ingredient_tag'] : null;

// SQLクエリ作成（カテゴリや食材がNULLの場合の扱いを修正）

/*
 SQLクエリ修正 GROUP_CONCAT関数を使って、複数のカテゴリや食材がある場合もまとめて取得

 追加
    GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name ASC SEPARATOR ', ') AS categories, 
    GROUP_CONCAT(DISTINCT mi.ingredient_name ORDER BY mi.ingredient_name ASC SEPARATOR ', ') AS ingredients
*/ 
$sql = "
    SELECT r.*, 
           GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name ASC SEPARATOR ', ') AS categories, 
           GROUP_CONCAT(DISTINCT mi.ingredient_name ORDER BY mi.ingredient_name ASC SEPARATOR ', ') AS ingredients
    FROM recipes r
    LEFT JOIN recipe_to_category rtc ON r.recipe_id = rtc.recipe_id
    LEFT JOIN categories c ON rtc.category_id = c.category_id
    LEFT JOIN recipe_to_ingredient rti ON r.recipe_id = rti.recipe_id
    LEFT JOIN main_ingredients mi ON rti.ingredient_id = mi.ingredient_id
    WHERE r.name LIKE :recipe_name
    AND (:category_tag IS NULL OR c.category_name = :category_tag)
    AND (:ingredient_tag IS NULL OR mi.ingredient_name = :ingredient_tag)
    GROUP BY r.recipe_id
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
    <title>レシピ検索</title>
</head>
<body>
    <h1>レシピ検索フォーム</h1>
    <form action="index.php" method="GET">
        <label for="recipe_name">レシピ名:</label>
        <input type="text" name="recipe_name" id="recipe_name" value="<?= htmlspecialchars($_GET['recipe_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <label for="category_tag">カテゴリタグ:</label>
        <input type="text" name="category_tag" id="category_tag" value="<?= htmlspecialchars($_GET['category_tag'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label for="ingredient_tag">主要食材タグ:</label>
        <input type="text" name="ingredient_tag" id="ingredient_tag" value="<?= htmlspecialchars($_GET['ingredient_tag'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <button type="submit">検索</button>
    </form>

    <h2>検索結果</h2>
    <?php if ($results): ?>
        <?php foreach ($results as $recipe):?>
            <h3><?= htmlspecialchars($recipe['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p>投稿日: <?= htmlspecialchars($recipe['date'], ENT_QUOTES, 'UTF-8') ?></p>
            <p>材料: <?= htmlspecialchars($recipe['ingredient'], ENT_QUOTES, 'UTF-8') ?></p>
            <p>手順: <?= htmlspecialchars($recipe['process'], ENT_QUOTES, 'UTF-8') ?></p>
            <!-- カテゴリ・食材タグを追加 -->
            <p>カテゴリタグ: <?= htmlspecialchars($recipe['categories'], ENT_QUOTES, 'UTF-8') ?></p>
            <p>主要食材タグ: <?= htmlspecialchars($recipe['ingredients'], ENT_QUOTES, 'UTF-8') ?></p>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>レシピが見つかりませんでした。</p>
    <?php endif; ?>
</body>
</html>
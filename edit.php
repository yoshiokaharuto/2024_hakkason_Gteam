<?php
// データベース接続ファイルを読み込む
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$resultMessage = '';
$errorMessages = [
    'name' => '',
    'ingredient' => '',
    'process' => ''
];

// レシピIDの取得
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recipe_id <= 0) {
    echo "レシピIDが指定されていません。";
    exit();
}

// レシピ情報を取得
$sql = "SELECT * FROM recipes WHERE recipe_id = :recipe_id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
$stm->execute();
$recipe = $stm->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "該当するレシピが見つかりません。";
    exit();
}

// 全てのカテゴリを取得
$sql_categories = "SELECT * FROM categories";
$stmt_categories = $pdo->prepare($sql_categories);
$stmt_categories->execute();
$allCategories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// このレシピに紐づいているカテゴリを取得
$sql_recipe_categories = "SELECT category_id FROM recipe_to_category WHERE recipe_id = :recipe_id";
$stmt_recipe_categories = $pdo->prepare($sql_recipe_categories);
$stmt_recipe_categories->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
$stmt_recipe_categories->execute();
$currentCategories = $stmt_recipe_categories->fetchAll(PDO::FETCH_COLUMN, 0);  // 現在のカテゴリID一覧

// 全ての主要食材を取得
$sql_ingredients = "SELECT * FROM main_ingredients";
$stmt_ingredients = $pdo->prepare($sql_ingredients);
$stmt_ingredients->execute();
$allIngredients = $stmt_ingredients->fetchAll(PDO::FETCH_ASSOC);

// このレシピに紐づいている主要食材を取得
$sql_recipe_ingredients = "SELECT ingredient_id FROM recipe_to_ingredient WHERE recipe_id = :recipe_id";
$stmt_recipe_ingredients = $pdo->prepare($sql_recipe_ingredients);
$stmt_recipe_ingredients->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
$stmt_recipe_ingredients->execute();
$currentIngredients = $stmt_recipe_ingredients->fetchAll(PDO::FETCH_COLUMN, 0);  // 現在の主要食材ID一覧

// POSTデータが存在するか確認して処理を行う
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 必須項目のバリデーション
    if (empty($_POST['name'])) {
        $errorMessages['name'] = 'レシピ名は必須です。';
    }
    if (empty($_POST['ingredient'])) {
        $errorMessages['ingredient'] = '材料は必須です。';
    }
    if (empty($_POST['process'])) {
        $errorMessages['process'] = '手順は必須です。';
    }

    // エラーがない場合に処理を行う
    if (empty(array_filter($errorMessages))) {
        // POSTデータを変数に格納
        $name = $_POST['name'];
        $genre = $_POST['genre'];
        $ingredient = $_POST['ingredient'];
        $time = $_POST['time'];
        $process = $_POST['process'];
        $note = isset($_POST['note']) ? $_POST['note'] : '';
        try {
            // トランザクション開始
            $pdo->beginTransaction();

            // レシピの更新処理
            $sql1 = "UPDATE recipes 
                SET name = :name, genre = :genre, ingredient = :ingredient, time = :time, process = :process, note = :note, date = NOW()
                WHERE recipe_id = :recipe_id";
            $stm1 = $pdo->prepare($sql1);
            $stm1->bindValue(':name', $name, PDO::PARAM_STR);
            $stm1->bindValue(':genre', $genre, PDO::PARAM_INT);
            $stm1->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
            $stm1->bindValue(':time', $time, PDO::PARAM_INT);
            $stm1->bindValue(':process', $process, PDO::PARAM_STR);
            $stm1->bindValue(':note', $note, PDO::PARAM_STR);
            $stm1->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stm1->execute();

            // カテゴリの削除と追加
            $sql_delete = "DELETE FROM recipe_to_category WHERE recipe_id = :recipe_id";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            if (isset($_POST['category_id']) && is_array($_POST['category_id'])) {
                foreach ($_POST['category_id'] as $category_id) {
                    $sql_insert = "INSERT INTO recipe_to_category (recipe_id, category_id) VALUES (:recipe_id, :category_id)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    $stmt_insert->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
                    $stmt_insert->bindValue(':category_id', intval($category_id), PDO::PARAM_INT);
                    $stmt_insert->execute();
                }
            }

            // 主要食材の削除と追加
            $sql_delete_ingredients = "DELETE FROM recipe_to_ingredient WHERE recipe_id = :recipe_id";
            $stmt_delete_ingredients = $pdo->prepare($sql_delete_ingredients);
            $stmt_delete_ingredients->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stmt_delete_ingredients->execute();

            if (isset($_POST['main_ingredient_id']) && is_array($_POST['main_ingredient_id'])) {
                foreach ($_POST['main_ingredient_id'] as $ingredient_id) {
                    $sql_insert_ingredients = "INSERT INTO recipe_to_ingredient (recipe_id, ingredient_id) VALUES (:recipe_id, :ingredient_id)";
                    $stmt_insert_ingredients = $pdo->prepare($sql_insert_ingredients);
                    $stmt_insert_ingredients->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
                    $stmt_insert_ingredients->bindValue(':ingredient_id', intval($ingredient_id), PDO::PARAM_INT);
                    $stmt_insert_ingredients->execute();
                }
            }

            // コミット
            $pdo->commit();
            $resultMessage = "レシピの情報が正常に更新されました！<br>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $resultMessage = "SQLエラー: " . $e->getMessage() . "<br>";
        }
    } else {
        $resultMessage = implode('<br>', array_filter($errorMessages)) . "<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシピ編集画面</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
    <header>
        <a href="index.php" title="レシピ一覧に戻る">
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
            <a href="logout.php" title="ログアウト">
                <span class="material-symbols-outlined">logout</span>
            </a>
        </div>
        <label id="sub-header-button-container">
            <input type="checkbox" id="sub-header-checkbox">
            <span class="material-symbols-outlined" id="sub-header-button">menu</span>
        </label>
    </header>

    <div id="sub-header">
        <ul>
            <a href="post.php">
                <li>
                    <span class="material-symbols-outlined">add_circle</span>
                    新規投稿
                </li>
            </a>
            <a href="settings.php">
                <li>
                    <span class="material-symbols-outlined">settings</span>
                    設定
                </li>
            </a>
            <a href="logout.php">
                <li>
                    <span class="material-symbols-outlined">logout</span>
                    ログアウト
                </li>
            </a>
        </ul>
    </div>
    
    <main>
        <div id="page-name-section">
            <h1>レシピ編集</h1>
            <p><?php echo $resultMessage; ?></p>
        </div>
        <form action="edit.php?id=<?= $recipe_id ?>" method="POST">
            <!-- レシピ名入力 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">edit</span>
                    レシピ名
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $recipe['name'], ENT_QUOTES, 'UTF-8') ?>" class="post-item">
                </label>
                <p class="error-message"><?php echo $errorMessages['name']; ?></p>
            </div>
            
            <!-- ジャンル選択 -->
            <div class="post-item-container">
                <span class="material-symbols-outlined">widgets</span>    
                ジャンル
                <div class="genre-group">
                    <div class="genre-option">
                        <input type="radio" name="genre" id="japanese" value="0" <?= (isset($_POST['genre']) && $_POST['genre'] == 0) || (!isset($_POST['genre']) && $recipe['genre'] == 0) ? 'checked' : '' ?>>
                        <label for="japanese">和風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="western" value="1" <?= (isset($_POST['genre']) && $_POST['genre'] == 1) || (!isset($_POST['genre']) && $recipe['genre'] == 1) ? 'checked' : '' ?>>
                        <label for="western">洋風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="chinese" value="2" <?= (isset($_POST['genre']) && $_POST['genre'] == 2) || (!isset($_POST['genre']) && $recipe['genre'] == 2) ? 'checked' : '' ?>>
                        <label for="chinese">中華風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="dessert" value="3" <?= (isset($_POST['genre']) && $_POST['genre'] == 3) || (!isset($_POST['genre']) && $recipe['genre'] == 3) ? 'checked' : '' ?>>
                        <label for="dessert">デザート</label>
                    </div>
                </div>
            </div>
            
            <!-- 所要時間 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">timer</span>
                    所要時間（分）
                    <input type="number" class="post-item" name="time" value="<?= htmlspecialchars($_POST['time'] ?? $recipe['time'], ENT_QUOTES, 'UTF-8') ?>" step="1" min="1">
                </label>
            </div>

            <!-- 主要食材選択 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">star</span>
                    主要食材
                </label>
                <div id="main-ingredient-container">
                    <select name="main_ingredient_id[]" class="post-item">
                        <?php foreach ($allIngredients as $ingredient): ?>  <!-- 変数名を $allIngredients に修正 -->
                            <option value="<?= $ingredient['ingredient_id']; ?>" 
                                <?= in_array($ingredient['ingredient_id'], $currentIngredients) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ingredient['ingredient_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?> <!-- ここで endforeach を正しく閉じる -->
                    </select>
                </div>
                <button type="button" id="add-main-ingredient" class="add-button">
                    <span class="material-symbols-outlined">add</span>
                    主要食材を追加
                </button>
            </div>

            <!-- 食材 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">grocery</span>
                    食材
                </label>
                <textarea name="ingredient" class="post-item"><?= htmlspecialchars($_POST['ingredient'] ?? $recipe['ingredient'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="error-message"><?php echo $errorMessages['ingredient']; ?></p>
            </div>

            <!-- 手順 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">format_list_numbered</span>
                    手順
                </label>
                <textarea name="process" class="post-item"><?= htmlspecialchars($_POST['process'] ?? $recipe['process'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <p class="error-message"><?php echo $errorMessages['process']; ?></p>
            </div>

            <!-- メモ -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">description</span>
                    メモ
                </label>
                <textarea name="note" class="post-item"><?= htmlspecialchars($_POST['note'] ?? $recipe['note'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <!-- カテゴリ選択 -->
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">sell</span>
                    カテゴリ
                </label>
                <div id="category-container">
                    <select name="category_id[]" class="post-item">
                        <?php foreach ($allCategories as $category): ?>  <!-- 変数名を $allCategories に修正 -->
                            <option value="<?= $category['category_id']; ?>" 
                                <?= in_array($category['category_id'], $currentCategories) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?> <!-- ここで endforeach を正しく閉じる -->
                    </select>            
                </div>
                <button type="button" id="add-category" class="add-button">
                    <span class="material-symbols-outlined">add</span>
                    カテゴリを追加
                </button>
            </div>
            <!-- 更新ボタン -->
            <div class="button-container">
                <a href="index.php" class="white-button">
                    <span class="material-symbols-outlined">undo</span>
                    レシピ一覧に戻る
                </a>
                <button type="submit" class="main-button">
                    <span class="material-symbols-outlined">check</span>
                    更新する
                </button>
            </div>
        </form>
    </main>

    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script src="js/script.js"></script>
    <script>
    document.getElementById('add-main-ingredient').addEventListener('click', function() {
        var container = document.getElementById('main-ingredient-container');
        
        var wrapperDiv = document.createElement('div');
        wrapperDiv.className = 'new-select-item';

        var newSelect = document.createElement('select');
        newSelect.name = 'main_ingredient_id[]';
        newSelect.className = 'post-item';
        newSelect.innerHTML = `<?php foreach ($allIngredients as $ingredient): ?><option value="<?php echo $ingredient['ingredient_id']; ?>"><?php echo $ingredient['ingredient_name']; ?></option><?php endforeach; ?>`;

        var removeButton = document.createElement('button');
        removeButton.innerHTML = '<span class="material-symbols-outlined">delete</span>';
        removeButton.className = 'remove-button';
        removeButton.type = 'button';

        removeButton.addEventListener('click', function() {
            container.removeChild(wrapperDiv);
        });

        wrapperDiv.appendChild(newSelect);
        wrapperDiv.appendChild(removeButton);

        container.appendChild(wrapperDiv);
    });

    document.getElementById('add-category').addEventListener('click', function() {
        var container = document.getElementById('category-container');
        
        var wrapperDiv = document.createElement('div');
        wrapperDiv.className = 'new-select-item';

        var newSelect = document.createElement('select');
        newSelect.name = 'category_id[]';
        newSelect.className = 'post-item';
        newSelect.innerHTML = `<?php foreach ($allCategories as $category): ?><option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option><?php endforeach; ?>`;

        var removeButton = document.createElement('button');
        removeButton.innerHTML = '<span class="material-symbols-outlined">delete</span>';
        removeButton.className = 'remove-button';
        removeButton.type = 'button';

        removeButton.addEventListener('click', function() {
            container.removeChild(wrapperDiv);
        });

        wrapperDiv.appendChild(newSelect);
        wrapperDiv.appendChild(removeButton);

        container.appendChild(wrapperDiv);
    });
    </script>


    <script src="js/script.js"></script>
</body>
</html>

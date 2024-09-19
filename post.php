<?php
// データベース接続ファイルを読み込む
require_once 'db_connect.php';

$resultMessage = '';
$errorMessages = [
    'name' => '',
    'ingredient' => '',
    'process' => ''
];

// データベースからカテゴリと主要食材を取得
$categories = [];
$ingredients = [];

try {
    // カテゴリの取得
    $sqlCategory = "SELECT category_id, category_name FROM categories";
    $stmCategory = $pdo->query($sqlCategory);
    $categories = $stmCategory->fetchAll(PDO::FETCH_ASSOC);

    // 主要食材の取得
    $sqlIngredient = "SELECT ingredient_id, ingredient_name FROM main_ingredients";
    $stmIngredient = $pdo->query($sqlIngredient);
    $ingredients = $stmIngredient->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $resultMessage = "データベース取得エラー: " . $e->getMessage() . "<br>";
}

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
        // レシピの投稿処理
        if (isset($_POST['name'], $_POST['genre'], $_POST['ingredient'], $_POST['time'], $_POST['process'], $_POST['category_id'], $_POST['main_ingredient_id'])) {
            // POSTデータを変数に格納
            $name = $_POST['name'];
            $genre = intval($_POST['genre']);
            $ingredient = $_POST['ingredient'];
            $time = intval($_POST['time']);
            $process = $_POST['process'];
            $note = isset($_POST['note']) ? $_POST['note'] : '';
            $category_ids = $_POST['category_id'];
            $main_ingredient_ids = $_POST['main_ingredient_id'];

            // 各カテゴリと主要食材を処理するためにループ
            try {
                $pdo->beginTransaction();
                $sql1 = "INSERT INTO recipes (name, genre, ingredient, time, process, note) 
                         VALUES (:name, :genre, :ingredient, :time, :process, :note)";
                $stm1 = $pdo->prepare($sql1);
                $stm1->bindValue(':name', $name, PDO::PARAM_STR);
                $stm1->bindValue(':genre', $genre, PDO::PARAM_INT);
                $stm1->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
                $stm1->bindValue(':time', $time, PDO::PARAM_INT);
                $stm1->bindValue(':process', $process, PDO::PARAM_STR);
                $stm1->bindValue(':note', $note, PDO::PARAM_STR);
                $stm1->execute();

                $recipeId = $pdo->lastInsertId();

                // カテゴリを処理
                foreach ($category_ids as $category_id) {
                $sql2 = "INSERT INTO recipe_to_category (recipe_id, category_id) VALUES (:recipe_id, :category_id)";
                $stm2 = $pdo->prepare($sql2);
                $stm2->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
                $stm2->bindValue(':category_id', intval($category_id), PDO::PARAM_INT);
                $stm2->execute();
            }

                // 主要食材を処理
                foreach ($main_ingredient_ids as $ingredient_id) {
                $sql3 = "INSERT INTO recipe_to_ingredient (recipe_id, ingredient_id) VALUES (:recipe_id, :ingredient_id)";
                $stm3 = $pdo->prepare($sql3);
                $stm3->bindValue(':recipe_id', $recipeId, PDO::PARAM_INT);
                $stm3->bindValue(':ingredient_id', intval($ingredient_id), PDO::PARAM_INT);
                $stm3->execute();
            }

                $pdo->commit();
                $resultMessage = "レシピが正常に投稿されました。";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $resultMessage = "投稿エラー: " . $e->getMessage() . "<br>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシピ投稿フォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
    <header>
        <a href="index.php" title="レシピ一覧に戻る">
            <h1 class="app-name">サービス名</h1>
        </a>
        <div id="header-icon-container">
            <a href="settings.php" title="設定">
                <span class="material-symbols-outlined">settings</span>
            </a>
            <a href="login.php" title="ログアウト"> <!-- 仮でログイン画面に飛びます -->
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
            <a href="settings.php">
                <li>
                    <span class="material-symbols-outlined">settings</span>
                    設定
                </li>
            </a>
            <a href="login.php"> <!-- 仮でログイン画面に飛びます -->
                <li>
                    <span class="material-symbols-outlined">logout</span>
                    ログアウト
                </li>
            </a>
        </ul>
    </div>
    
    <main>
        <div id="page-name-section">
            <h1>新規投稿</h1>
            <p><?php echo $resultMessage; ?></p>
        </div>

        <form action="index.php" method="POST">
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">edit</span>
                    レシピ名
                    <input type="text" name="name" placeholder="ちょい足し卵かけご飯" class="post-item">
                    <p class="error-message"><?php echo $errorMessages['name']; ?></p>
                </label>
            </div>
            <div class="post-item-container">
                <span class="material-symbols-outlined">widgets</span>    
                ジャンル
                <div class="genre-group">
                    <div class="genre-option">
                        <input type="radio" name="genre" id="japanese" value="1" checked>
                        <label for="japanese">和風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="western" value="2">
                        <label for="western">洋風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="chinese" value="3">
                        <label for="chinese">中華風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="dessert" value="4">
                        <label for="dessert">デザート</label>
                    </div>
                </div>
            </div>
            <div class="post-item-container">
                <label>
                <span class="material-symbols-outlined">timer</span>
                    所要時間（分）
                    <input type="number" class="post-item" name="time" value="1" step="1" min="1">
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">grocery</span>
                    食材
                    <textarea name="ingredient" placeholder="・卵 - 1個
・ご飯 - 150g
・焼き肉のタレ - 大さじ1" class="post-item"></textarea>
                    <p class="error-message"><?php echo $errorMessages['ingredient']; ?></p>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">star</span>
                    主要食材
                </label>
                <div id="main-ingredient-container">
                    <select name="main_ingredient_id[]" class="post-item">
                        <option value="">選択してください</option>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <option value="<?php echo $ingredient['ingredient_id']; ?>">
                                <?php echo $ingredient['ingredient_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="add-main-ingredient">主要食材を追加</button>
            </div>
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">format_list_numbered</span>
                    手順
                    <textarea name="process" placeholder="①茶碗にご飯を盛り、卵を割り入れます。..." class="post-item"></textarea>
                    <p class="error-message"><?php echo $errorMessages['process']; ?></p>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">description</span>
                    メモ
                    <textarea name="note" placeholder="このレシピはお弁当にも最適です。" class="post-item"></textarea>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    <span class="material-symbols-outlined">sell</span>
                    カテゴリ
                </label>
                <div id="category-container">
                    <select name="category_id[]" class="post-item">
                        <option value="">選択してください</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="add-category">カテゴリを追加</button>
            </div>
            <div class="button-container">
                <a href="index.php" class="white-button">
                    <span class="material-symbols-outlined">undo</span>
                    レシピ一覧に戻る
                </a>
                <button type="submit" class="main-button">
                    <span class="material-symbols-outlined">send</span>
                    投稿する
                </button>
            </div>
        </form>
    </main>

    <footer>
        <h1 class="app-name">サービス名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script type="text/javascript" src="js/script.js"></script>
    <script>
    document.getElementById('add-main-ingredient').addEventListener('click', function() {
        var container = document.getElementById('main-ingredient-container');
        
        // 新しいセレクトボックスを作成
        var newSelect = document.createElement('select');
        newSelect.name = 'main_ingredient_id[]';
        newSelect.className = 'post-item';
        newSelect.innerHTML = '<option value="">選択してください</option><?php foreach ($ingredients as $ingredient): ?><option value="<?php echo $ingredient['ingredient_id']; ?>"><?php echo $ingredient['ingredient_name']; ?></option><?php endforeach; ?>';

        // 削除ボタンを作成
        var removeButton = document.createElement('button');
        removeButton.textContent = '削除';
        removeButton.className = 'remove-button';
        removeButton.type = 'button';

        // 削除ボタンにクリックイベントを追加
        removeButton.addEventListener('click', function() {
            container.removeChild(newSelect);
            container.removeChild(removeButton);
        });

        // コンテナにセレクトボックスと削除ボタンを追加
        container.appendChild(newSelect);
        container.appendChild(removeButton);
    });

    document.getElementById('add-category').addEventListener('click', function() {
        var container = document.getElementById('category-container');
        
        // 新しいセレクトボックスを作成
        var newSelect = document.createElement('select');
        newSelect.name = 'category_id[]';
        newSelect.className = 'post-item';
        newSelect.innerHTML = '<option value="">選択してください</option><?php foreach ($categories as $category): ?><option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option><?php endforeach; ?>';

        // 削除ボタンを作成
        var removeButton = document.createElement('button');
        removeButton.textContent = '削除';
        removeButton.className = 'remove-button';
        removeButton.type = 'button';

        // 削除ボタンにクリックイベントを追加
        removeButton.addEventListener('click', function() {
            container.removeChild(newSelect);
            container.removeChild(removeButton);
        });

        // コンテナにセレクトボックスと削除ボタンを追加
        container.appendChild(newSelect);
        container.appendChild(removeButton);
    });
</script>

</body>
</html>

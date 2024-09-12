<?php
// データベース接続ファイルを読み込む
require_once 'db_connect.php';

$resultMessage = '';
$errorMessages = [
    'name' => '',
    'ingredient' => '',
    'process' => ''
];

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
        if (isset($_POST['name'], $_POST['genre'], $_POST['ingredient'], $_POST['time'], $_POST['process'])) {
            // POSTデータを変数に格納
            $name = $_POST['name'];
            $genre = $_POST['genre'];
            $ingredient = $_POST['ingredient'];
            $time = $_POST['time'];
            $process = $_POST['process'];
            $note = isset($_POST['note']) ? $_POST['note'] : '';

            // レシピを `recipes` テーブルに挿入
            $sql1 = "INSERT INTO recipes (name, genre, ingredient, time, process, note) 
                     VALUES (:name, :genre, :ingredient, :time, :process, :note)";

            try {
                $stm1 = $pdo->prepare($sql1);
                $stm1->bindValue(':name', $name, PDO::PARAM_STR);
                $stm1->bindValue(':genre', $genre, PDO::PARAM_INT);
                $stm1->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
                $stm1->bindValue(':time', $time, PDO::PARAM_INT);
                $stm1->bindValue(':process', $process, PDO::PARAM_STR);
                $stm1->bindValue(':note', $note, PDO::PARAM_STR);

                if ($stm1->execute()) {
                    $resultMessage = "レシピが正常に投稿されました！<br>";
                } else {
                    $resultMessage = "レシピの投稿に失敗しました。<br>";
                }
            } catch (PDOException $e) {
                $resultMessage = "SQLエラー: " . $e->getMessage() . "<br>";
            }
        }

        // カテゴリの投稿処理
        if (isset($_POST['category_name'])) {
            $category_name = $_POST['category_name'];
            $sql2 = "INSERT INTO categories (category_name) VALUES (:category_name)";
            try {
                $stm2 = $pdo->prepare($sql2);
                $stm2->bindValue(':category_name', $category_name, PDO::PARAM_STR);
                $stm2->execute();
            } catch (PDOException $e) {
            }
        }

        // 主要食材の投稿処理）
        if (isset($_POST['ingredient_name'])) {
            $ingredient_name = $_POST['ingredient_name'];
            $sql3 = "INSERT INTO main_ingredients (ingredient_name) VALUES (:ingredient_name)";
            try {
                $stm3 = $pdo->prepare($sql3);
                $stm3->bindValue(':ingredient_name', $ingredient_name, PDO::PARAM_STR);
                $stm3->execute();
            } catch (PDOException $e) {
            }
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
    <title>レシピ投稿フォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
    <header>
        <a href="index.php">
            <h1 class="app-name">アプリ名</h1>
        </a>
    </header>
    
    <main>
        <div id="page-name-section">
            <h1>新規投稿</h1>
            <p><?php echo $resultMessage; ?></p>
        </div>

        <form action="post.php" method="POST">
            <div class="post-item-container">
                <label>
                    レシピ名
                    <input type="text" name="name" placeholder="ちょい足し卵かけご飯" class="post-item">
                </label>
                <p class="error-message"><?php echo $errorMessages['name']; ?></p>
            </div>
            <div class="post-item-container">
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
                    所要時間（分）
                    <input type="number" class="post-item" name="time" value="1" step="1" min="1">
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    材料
                    <textarea name="ingredient" placeholder="・卵 - 1個
・ご飯 - 150g
・焼き肉のタレ - 大さじ1" class="post-item"></textarea>
                </label>
                <p class="error-message"><?php echo $errorMessages['ingredient']; ?></p>
            </div>
            <div>
                <label>主要食材</label>
                <input type="text" name="ingredient_name">
            </div>
            <div class="post-item-container">
                <label>
                    手順
                    <textarea name="process" placeholder="①茶碗にご飯を盛る
②卵を割ってご飯にかける
③焼き肉のタレをかける" class="post-item"></textarea>
                </label>
                <p class="error-message"><?php echo $errorMessages['process']; ?></p>
            </div>
            <div class="post-item-container">
                <label>
                    メモ
                    <textarea name="note" placeholder="〇〇社のタレがおすすめです。" class="post-item"></textarea>
                </label>
            </div>
            <div>
                <label>カテゴリ名</label>
                <input type="text" name="category_name">
            </div>
            <div class="button-container">
                <a href="index.php" class="white-button">投稿一覧に戻る</a>
                <input type="submit" value="投稿する" class="main-button">
            </div>
        </form>
    </main>

    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>
</body>
</html>

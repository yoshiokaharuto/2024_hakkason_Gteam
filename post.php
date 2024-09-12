<?php
// データベース接続ファイルを読み込む
require_once 'db_connect.php';

$resultMessage = "";

// POSTデータがすべて存在するか確認
if (isset($_POST['name']) && isset($_POST['genre']) && isset($_POST['ingredient']) && isset($_POST['time']) && isset($_POST['process'])) {
    // POSTデータを変数に格納
    $name = $_POST['name'];
    $genre = $_POST['genre'];
    $ingredient = $_POST['ingredient'];
    $time = $_POST['time'];
    $process = $_POST['process'];
    $note = isset($_POST['note']) ? $_POST['note'] : '';

    // SQL文を作成（プレースホルダーを使用）
    $sql = "INSERT INTO recipes (name, genre, ingredient, time, process, note) 
            VALUES (:name, :genre, :ingredient, :time, :process, :note)";

    // SQL文をプリペアして実行
    try {
        $stm = $pdo->prepare($sql);
        // プレースホルダーに値をバインド
        $stm->bindValue(':name', $name, PDO::PARAM_STR);
        $stm->bindValue(':genre', $genre, PDO::PARAM_INT);
        $stm->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
        $stm->bindValue(':time', $time, PDO::PARAM_INT);
        $stm->bindValue(':process', $process, PDO::PARAM_STR);
        $stm->bindValue(':note', $note, PDO::PARAM_STR);

        // SQL文を実行してデータベースにデータを挿入
        if ($stm->execute()) {
            $resultMessage = "レシピが正常に投稿されました！";
        } else {
            $resultMessage = "レシピの投稿に失敗しました。";
        }
    } catch (PDOException $e) {
        // SQLエラー発生時の処理
        echo "SQLエラー: " . $e->getMessage();
    }
} else {
    // 必須項目が不足している場合のメッセージ
    $resultMessage = "全ての必須項目を入力してください。";
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
            <h1 class="app-name">
                アプリ名
            </h1>
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
                    <input type="text" name="name" placeholder="ちょい足し卵かけご飯" class="post-item" required>
                </label>
            </div>
            <div class="post-item-container">
                ジャンル
                <div class="genre-group">
                    <div class="genre-option">
                        <input type="radio" name="genre" id="japanese" value="0" checked>
                        <label for="japanese">和風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="western" value="1">
                        <label for="western">洋風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="chinese" value="2">
                        <label for="chinese">中華風</label>
                    </div>
                    <div class="genre-option">
                        <input type="radio" name="genre" id="dessert" value="3">
                        <label for="dessert">デザート</label>
                    </div>
                </div>
            </div>
            <div class="post-item-container">
                <label>
                    所要時間（分）
                    <input type="number" class="post-item" name="time" value="1" step="1" min="1" required>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    材料
                    <textarea name="ingredient" placeholder="・卵 - 1個
・ご飯 - 150g
・焼き肉のタレ - 大さじ1" class="post-item" required></textarea>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    手順
                    <textarea name="process" placeholder="①茶碗にご飯を盛る
②卵を割ってご飯にかける
③焼き肉のタレをかける" class="post-item" required></textarea>
                </label>
            </div>
            <div class="post-item-container">
                <label>
                    メモ
                    <textarea name="note" placeholder="〇〇社のタレがおすすめです。" class="post-item"></textarea>
                </label>
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

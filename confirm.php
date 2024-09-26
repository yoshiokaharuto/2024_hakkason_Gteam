<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$resultMessage = '';
$errorMessages = [];

// POSTデータの存在確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: post.php");
    exit();
}

// POSTデータを変数に格納
$name = $_POST['name'] ?? '';
$genre = $_POST['genre'] ?? '';
$ingredient = $_POST['ingredient'] ?? '';
$time = $_POST['time'] ?? '';
$process = $_POST['process'] ?? '';
$note = $_POST['note'] ?? '';
$category_ids = $_POST['category_id'] ?? [];
$main_ingredient_ids = $_POST['main_ingredient_id'] ?? [];

// セッションにデータを保存
if (!isset($_SESSION['post_data'])) {
    $_SESSION['post_data'] = [
        'name' => $name,
        'genre' => $genre,
        'ingredient' => $ingredient,
        'time' => $time,
        'process' => $process,
        'note' => $note,
        'category_id' => $category_ids,
        'main_ingredient_id' => $main_ingredient_ids,
    ];
}

// データが正しいかどうかを検証する
if (empty($name) || empty($ingredient) || empty($process)) {
    $resultMessage = '入力内容にエラーがあります。もう一度お試しください。';
}

// 確認画面に表示するジャンル
$genres = [
    1 => "和風",
    2 => "洋風",
    3 => "中華風",
    4 => "お菓子・デザート"
];

// 投稿処理を行う
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        require_once 'db_connect.php';

        // トランザクション開始
        $pdo->beginTransaction();

        // レシピの挿入
        $sql = "INSERT INTO recipes (name, genre, ingredient, time, process, note, user_id) 
                VALUES (:name, :genre, :ingredient, :time, :process, :note, :user_id)";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':name', $name, PDO::PARAM_STR);
        $stm->bindValue(':genre', intval($genre), PDO::PARAM_INT);
        $stm->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
        $stm->bindValue(':time', intval($time), PDO::PARAM_INT);
        $stm->bindValue(':process', $process, PDO::PARAM_STR);
        $stm->bindValue(':note', $note, PDO::PARAM_STR);
        $stm->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stm->execute();

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

        // トランザクションコミット
        $pdo->commit();

        // 投稿成功メッセージ
        $resultMessage = "レシピが正常に投稿されました。";
        unset($_SESSION['post_data']); // 成功したらセッションデータをクリア
        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $resultMessage = "投稿エラー: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿確認画面</title>
    <link rel="icon" href="img/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>投稿確認画面</h1>
    </header>
    <main>
        <h2>以下の内容で投稿します。</h2>
        <?php if ($resultMessage): ?>
            <p style="color: red;"><?php echo htmlspecialchars($resultMessage); ?></p>
        <?php endif; ?>
        <p><strong>レシピ名:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>ジャンル:</strong> <?php echo htmlspecialchars($genres[$genre] ?? ''); ?></p>
        <p><strong>食材:</strong> <pre><?php echo htmlspecialchars($ingredient); ?></pre></p>
        <p><strong>所要時間:</strong> <?php echo htmlspecialchars($time); ?> 分</p>
        <p><strong>手順:</strong> <pre><?php echo htmlspecialchars($process); ?></pre></p>
        <p><strong>メモ:</strong> <pre><?php echo htmlspecialchars($note); ?></pre></p>

        <form action="post.php" method="POST">
            <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
            <input type="hidden" name="genre" value="<?php echo htmlspecialchars($genre); ?>">
            <input type="hidden" name="ingredient" value="<?php echo htmlspecialchars($ingredient); ?>">
            <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
            <input type="hidden" name="process" value="<?php echo htmlspecialchars($process); ?>">
            <input type="hidden" name="note" value="<?php echo htmlspecialchars($note); ?>">
            <input type="hidden" name="category_id[]" value="<?php echo htmlspecialchars(implode(',', $category_ids)); ?>">
            <input type="hidden" name="main_ingredient_id[]" value="<?php echo htmlspecialchars(implode(',', $main_ingredient_ids)); ?>">

            <button type="submit" name="submit">この内容で投稿する</button>
            <a href="post.php">編集する</a>
        </form>
    </main>
</body>
</html>
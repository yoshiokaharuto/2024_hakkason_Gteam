<?php
require_once "./db_connect.php";
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $recipe_id = $_GET['id'];

    // 削除確認のためのレシピ詳細を取得
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
    $stm->bindValue(':id', $recipe_id, PDO::PARAM_INT);
    $stm->execute();
    $data = $stm->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        // レシピが存在しない場合はindexへリダイレクト
        header("Location: index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        // レシピの削除
        try {
            $pdo->beginTransaction();

            // recipe_to_category から削除
            $sql1 = "DELETE FROM recipe_to_category WHERE recipe_id = :recipe_id";
            $stm1 = $pdo->prepare($sql1);
            $stm1->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stm1->execute();

            // recipe_to_ingredient から削除
            $sql2 = "DELETE FROM recipe_to_ingredient WHERE recipe_id = :recipe_id";
            $stm2 = $pdo->prepare($sql2);
            $stm2->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stm2->execute();

            // recipes から削除
            $sql3 = "DELETE FROM recipes WHERE recipe_id = :recipe_id";
            $stm3 = $pdo->prepare($sql3);
            $stm3->bindValue(':recipe_id', $recipe_id, PDO::PARAM_INT);
            $stm3->execute();

            $pdo->commit();

            // 削除成功後、indexにリダイレクト
            header("Location: index.php?message=レシピが削除されました");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errorMessage = "削除エラー: " . $e->getMessage();
        }
    }
} else {
    header("Location: index.php");
    exit();
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
    <title>削除確認画面</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
    <style>
        :root {
            --main-color: #<?= $_SESSION['theme']['main'] ?>;
            --sub-color: #<?= $_SESSION['theme']['sub'] ?>;
            --background-color: #<?= $_SESSION['theme']['background'] ?>;
            --text-color: #<?= $_SESSION['theme']['text'] ?>;
            --invert-text-color: #<?= $_SESSION['theme']['invert-text'] ?>;
        }
    </style> 
</head>
<body>
    <header>
        <a href="index.php" title="レシピ一覧に戻る">
            <h1 class="app-name">
                アプリ名
            </h1>
        </a>

        <div id="header-icon-container">
<?php if(!isset($_SESSION['user_id'])) { // 未ログイン状態 ?>
            <p title='レシピを投稿するにはログインが必要です' class='cant-click'>
                <span class='material-symbols-outlined'>add_circle</span>
            </p>
            <a href='login.php' title='ログイン'>
                <span class='material-symbols-outlined'>login</span>
            </a>
<?php } else { // ログイン状態 ?>
            <a href="post.php" title="新規投稿">
                <span class="material-symbols-outlined">add_circle</span>
            </a>
            <a href="settings.php" title="設定">
                <span class="material-symbols-outlined">settings</span>
            </a>
            <a href="logout.php" title="ログアウト">
                <span class="material-symbols-outlined">logout</span>
            </a>
<?php } ?>
        </div>
        <label id="sub-header-button-container">
            <input type="checkbox" id="sub-header-checkbox">
            <span class="material-symbols-outlined" id="sub-header-button">menu</span>
        </label>
    </header>

    <div id="sub-header">
        <ul>
<?php if(!isset($_SESSION['user_id'])) { // 未ログイン状態 ?>
            <li  title='レシピを投稿するにはログインが必要です' class='cant-click'>
                <span class='material-symbols-outlined'>add_circle</span>
                新規投稿
            </li>
            <a href='login.php'>
                <li>
                    <span class='material-symbols-outlined'>login</span>
                    ログイン
                </li>
            </a>
<?php } else { // ログイン状態 ?>
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
<?php } ?>
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
                <span class="material-symbols-outlined">timer</span>
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
            <p class='recipe-userAndDate'>
            <span class="material-symbols-outlined">person</span>
            <?= htmlspecialchars($data['user_id'], ENT_QUOTES, 'UTF-8') ?><br>
            <span class="material-symbols-outlined">calendar_today</span>
            <?= htmlspecialchars($data['date'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <p class="warning-text">本当にこのレシピを削除しますか？</p>
        <div class="button-container">
            <form action="delete.php" method="POST">
                <button type="submit" name="confirm" class="main-button">
                    <span class="material-symbols-outlined">delete</span>
                    削除する
                </button>
            </form>

            <a href="detail.php?id=<?= $recipe_id ?>" class="white-button">
                <span class="material-symbols-outlined">undo</span>
                レシピ詳細に戻る
            </a>
        </div>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

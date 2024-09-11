<?php
require_once "./db_connect.php";

$sql = "SELECT name,date,genre,ingredient,time FROM recipes";
$stmt = $pdo -> prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一覧・検索画面</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
    <header>
        <h1 class="app-name">アプリ名</h1>
        <a href="#">
            <span class="material-symbols-outlined">
                add_circle
            </span>
        </a>
    </header>

    <main>
        <div id="searchbox-container">
            <input type="text" placeholder="検索したいレシピ名を入力…">
            <button type="submit">
                <span class="material-symbols-outlined">
                    search
                </span>
            </button>
        </div>
        <?php
        foreach($results as $result){
            echo "<div class='recipe-card'>";
            echo "<div class='recipe-name-section'>";
            echo "<h1 class='recipe-name'>" . $result['name'] . "</h1>";
        }
        ?>
            </div>
            <p class="recipe-time">
                <span class="material-symbols-outlined">
                    timer
                </span>
                1分
            </p>
            <p class="recipe-ingredient">・材料<br>・材料<br>・材料</p>
            <p class="recipe-date">2024/09/11 10:30</p>
        </div>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>
</body>
</html>
<?php
require_once "db_connect.php";

$result = ['recipe_id' => '', 'name' => '', 'date' => '', 'genre' => '', 'ingredient' => '', 'time' => '','process' => '', 'note' => ''];

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM recipes WHERE recipe_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "詳細を表示できません";
    exit();
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
    </header>

    <main>
        <div class="recipe-card">
            <div class="recipe-name-section">
                <h1 class="recipe-name"><?php echo htmlspecialchars($result['name'])?></h1>
                <p class="recipe-genre"><?php echo htmlspecialchars($result['genre'])?></p>
            </div>
            <p class="recipe-time">
                <span class="material-symbols-outlined">
                    timer
                </span>
                <?php echo htmlspecialchars($result['time'])?>
            </p>
            <p class="recipe-date"><?php echo htmlspecialchars($result['date'])?></p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>材料</p>
                <button onclick="copyButton('recipe-ingredient')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-ingredient">・材料<br>・材料<br>・材料</p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>手順</p>
                <button onclick="copyButton('recipe-process')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-process"><?php echo htmlspecialchars($result['name'])?></p>
        </div>
        <div class="recipe-information">
            <div class="recipe-information-title-section">
                <p>メモ</p>
                <button onclick="copyButton('recipe-note')">
                    <span class="material-symbols-outlined">
                        content_copy
                    </span>
                </button>
            </div>
            <p class="recipe-information-content" id="recipe-note">メモ</p>
        </div>
        <div class="button-container">
            <a href="index.php" class="white-button">投稿一覧に戻る</a>
            <button onclick="copyAllButton()" class="main-button">このレシピをコピー</button>
        </div>
    </main>
    
    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script>
        function copyButton(elementId) {
            var element = document.getElementById(elementId);
            navigator.clipboard.writeText(element.innerText);
        }

        function copyAllButton() {
            // TODO : 現在開いているレシピの情報を項目名とともに一括でクリップボードにコピーする処理
        }
    </script>
</body>
</html>
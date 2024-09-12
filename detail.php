<?php
    require_once "./db_connect.php";

    if(isset($_GET['id'])) {
        $sql = "SELECT * FROM recipes WHERE recipe_id = :id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
        $stm->execute();
        $data = $stm->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: index.php");
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
                <span class="material-symbols-outlined">
                    timer
                </span>
                <?= preoutput($data['time']) ?>分
            </p>
            <p class='recipe-category'><?= $data['category'] ?></p>
            <p class='recipe-mainIngredient'><?= $data['main_ingredient'] ?></p>
            <p class="recipe-date">
                <?= preoutput($data['date']) ?>
            </p>
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
            <p class="recipe-information-content" id="recipe-ingredient">
                <?= preoutput($data['ingredient']) ?>
            </p>
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
            <p class="recipe-information-content" id="recipe-process">
                <?= preoutput($data['process']) ?>
            </p>
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
            <p class="recipe-information-content" id="recipe-note">
                <?= !empty($data['note']) ? preoutput($data['note']) : "(なし)" ?>
            </p>
        </div>
        <div id="recipe-dataToCopy">
            レシピ名 : <?= preoutput($data['name']) ?><br>
            ジャンル : <?php
                            switch($data["genre"]) {
                                case 0:
                                    echo "和風";
                                    break;
                                case 1:
                                    echo "洋風";
                                    break;
                                case 2:
                                    echo "中華風";
                                    break;
                                case 3:
                                    echo "お菓子・デザート";
                                    break;
                                default:
                                    echo $data['genre'];
                                    break;
                            }
                        ?><br>
            所要時間 : <?= preoutput($data['time']) ?>分<br>
            材料 :<br><?= preoutput($data['ingredient']) ?><br>
            主要食材 : <?= preoutput($data['main-ingredient']) ?><br>
            手順 : <br><?= preoutput($data['process']) ?><br>
            メモ : <br><?= preoutput($data['note']) ?><br>
            カテゴリタグ : <?= preoutput($data['category_tag']) ?>
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
            navigator.clipboard.writeText(document.getElementById('recipe-dataToCopy').innerText);
        }
    </script>
</body>
</html>
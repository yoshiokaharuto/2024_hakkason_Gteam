<?php
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン画面</title>
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
    
    <main id="login-main">
        <div id="login-container">
            <div id="page-name-section">
                <h1>ログイン</h1>
            </div>

            <form action="" method="POST">
                <div class="post-item-container">
                    <label>
                        ID
                        <input type="text" name="id" placeholder="IDを入力" class="post-item">
                    </label>
                </div>
                <div class="post-item-container">
                    <label>
                        パスワード
                        <input type="password" name="password" placeholder="パスワードを入力" class="post-item">
                    </label>
                </div>
                <div class="button-container-withLink">
                    <p class="link-message">
                        <a href="user_register.php">新規登録はこちらから</a>
                    </p>
                    <div class="button-container">
                        <a href="index.php" class="white-button">
                            <span class="material-symbols-outlined">
                                undo
                            </span>
                            レシピ一覧に戻る
                        </a>
                        <button type="submit" class="main-button">
                            <span class="material-symbols-outlined">
                                login
                            </span>
                            ログイン
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>
</body>
</html>

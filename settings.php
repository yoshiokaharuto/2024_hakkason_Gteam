<?php
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定画面</title>
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
            <h1>設定</h1>
        </div>
        <div class="settings-section">
            <h2>パスワードを変更</h2>
            <form action="" method="POST">
                <div class="post-item-container">
                    <label>
                        現在のパスワード
                        <input type="text" name="currentPassword" placeholder="現在のパスワードを入力してください" class="post-item">
                    </label>
                </div>
                <div class="post-item-container">
                    <label>
                        新しいパスワード
                        <input type="password" name="newPassword" placeholder="新しいパスワードを入力してください" class="post-item">
                    </label>
                </div>
                <div class="post-item-container">
                    <label>
                        新しいパスワード(確認)
                        <input type="password" name="newPasswordCheck" placeholder="確認の為もう一度入力してください" class="post-item">
                    </label>
                </div>
                <div class="button-container">
                    <input type="submit" value="変更" class="main-button">
                </div>
            </form>
        </div>

        <div class="settings-section">
            <h2>テーマを変更</h2>
            <form action="" method="POST">
                <div class="post-item-container">
                    <label>
                        <select name="theme" class="post-item">
                            <option value="orange">オレンジ</option>
                            <option value="blue">ブルー</option>
                            <option value="gray">グレー</option>
                        </select>
                    </label>
                </div>
                <div class="button-container">
                    <input type="submit" value="変更" class="main-button">
                </div>
            </form>
        </div>

        <div class="button-container">
            <a href="index.php" class="white-button">レシピ一覧に戻る</a>
            <a href="" class="main-button">ログアウト</a>
        </div>
    </main>

    <footer>
        <h1 class="app-name">アプリ名</h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>
</body>
</html>

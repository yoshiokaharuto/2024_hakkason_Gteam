<?php
    require_once "./db_connect.php";

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $sql = "SELECT * FROM theme";
    $stm = $pdo->prepare($sql);
    $stm->execute();
    $result = $stm->fetchAll(PDO::FETCH_ASSOC);

    $themeValues = array_column($result, 'theme_id');

    if(isset($_POST['theme_id']) && in_array((int)$_POST['theme_id'], $themeValues, true)) {
        $theme_id = $_POST['theme_id'];
        $sql = "SELECT * FROM theme WHERE theme_id = :theme_id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':theme_id', $theme_id, PDO::PARAM_INT);
        $stm->execute();
        $_SESSION['theme'] = $stm->fetch(PDO::FETCH_ASSOC);

        try {
            $sql = "UPDATE users SET theme_id = :theme_id WHERE user_id = :user_id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':theme_id', $theme_id, PDO::PARAM_INT);
            $stm->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stm->execute();
            $resultMessage = 'テーマを変更しました！';
        } catch(PDOException $e) {
            $resultMessage = 'sqlエラー:'.$e->getMessage();
        }
    }

    if(isset($_POST['currentPassword']) && isset($_POST['newPassword']) && isset($_POST['newPasswordCheck'])) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $newPasswordCheck = $_POST['newPasswordCheck'];

        $stm = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
        $stm->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stm->execute();
        $DBpassword = $stm->fetch(PDO::FETCH_ASSOC);

        if(strcmp($newPassword, $newPasswordCheck) !== 0) {
            $resultMessage = '新しいパスワードと新しいパスワード(確認)が一致しません。\nもう一度入力してください。';
        } elseif(password_verify($currentPassword, $DBpassword['password'])) {
            try {
                $newPassword_hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET password = :newPassword_hash WHERE user_id = :user_id";
                $stm = $pdo->prepare($sql);
                $stm->bindValue(':newPassword_hash', $newPassword_hash, PDO::PARAM_STR);
                $stm->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stm->execute();
                $resultMessage = 'パスワードを変更しました！';
            } catch(PDOException $e) {
                $resultMessage = 'sqlエラー:'.$e->getMessage();
            }
        } else {
            $resultMessage = 'パスワードを変更できませんでした。';
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>設定画面</title>
    <link rel="icon" href="img/favicon.ico">
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
                <img src="img/logo.png">
            </h1>
        </a>
        <div id="header-icon-container">
            <a href="post.php" title="新規投稿">
                <span class="material-symbols-outlined">add_circle</span>
            </a>
            <a href="logout.php" title="ログアウト">
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
            <a href="post.php">
                <li>
                    <span class="material-symbols-outlined">add_circle</span>
                    新規投稿
                </li>
            </a>
            <a href="logout.php">
                <li>
                    <span class="material-symbols-outlined">logout</span>
                    ログアウト
                </li>
            </a>
        </ul>
    </div>
    
    <main>
        <div id="page-name-section">
            <h1>設定</h1>
        </div>
        <div class="settings-section">
            <h2>
                <span class="material-symbols-outlined">lock</span>
                パスワードを変更
            </h2>
            <form action="settings.php" method="POST">
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
                    <button type="submit" class="sub-button">
                        <span class="material-symbols-outlined">check</span>
                        変更
                    </button>
                </div>
            </form>
        </div>

        <div class="settings-section">
            <h2>
                <span class="material-symbols-outlined">palette</span>
                テーマを変更
            </h2>
            <form action="settings.php" method="POST">
                <div class="post-item-container">
                    <label>
                        <select name="theme_id" class="post-item">
                            <?php foreach($result as $data): ?>
                                <option value="<?= $data['theme_id']?>" <?= $_SESSION['theme']['theme_id'] == $data['theme_id'] ? 'selected' : '' ?>>
                                <?= $data['name'] ?><?= $_SESSION['theme']['theme_id'] == $data['theme_id'] ? '【使用中】' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="button-container">
                    <button type="submit" class="sub-button">
                        <span class="material-symbols-outlined">check</span>
                        変更
                    </button>
                </div>
            </form>
        </div>

        <div class="button-container">
            <a href="logout.php" class="main-button">
                <span class="material-symbols-outlined">logout</span>
                ログアウト
            </a>
            <a href="index.php" class="white-button">
                <span class="material-symbols-outlined">undo</span>
                レシピ一覧に戻る
            </a>
        </div>
    </main>

    <footer>
        <h1 class="app-name">
            <img src="img/logo.png">
        </h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <?php if (!empty($resultMessage)): ?>
        <script>
            alert('<?php echo $resultMessage; ?>');
        </script>
    <?php endif; ?>
    <script src="js/script.js"></script>
</body>
</html>

<?php

require_once "./db_connect.php";
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$resultMessage = '';
$errorMessages = [
    'user_id' => '',
    'password' => ''
];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    //バリデーション
    if(empty($_POST['user_id'])) {
        $errorMessages['user_id'] = 'idを入力してください';
    }
    if(empty($_POST['password'])) {
        $errorMessages['password'] = 'パスワードを入力してください';
    }

    if(isset($_POST['user_id'],$_POST['password'])) {
        $user_id = $_POST['user_id'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            //パスワードの確認
            if (password_verify($password, $row['password'])){
                $_SESSION['user_id'] = $user_id;
                $theme_id = $row['theme_id'];

                $sql = "SELECT * FROM theme WHERE theme_id = :theme_id";
                $stm = $pdo->prepare($sql);
                $stm->bindValue(':theme_id', $theme_id, PDO::PARAM_INT);
                $stm->execute();
                $theme = $stm->fetch(PDO::FETCH_ASSOC);
                $_SESSION['theme'] = $theme;

                //セッションidを再生成
                session_regenerate_id(true);
                header("Location:index.php");
                exit();
            } else {
                $resultMessage = "無効なidまたはパスワードです。";
            }
        }
    }
}
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
        <a href="index.php" title="レシピ一覧に戻る">
            <h1 class="app-name">アプリ名</h1>
        </a>
    </header>
    
    <main id="login-main">
        <div id="login-container">
            <div id="page-name-section">
                <h1>ログイン</h1>
            </div>

            <form action="login.php" method="POST">
                <div class="post-item-container">
                    <label>
                        ID
                        <input type="text" name="user_id" placeholder="IDを入力" class="post-item">
                        <p><?php echo $errorMessages['user_id'];?></p>
                    </label>
                </div>
                <div class="post-item-container">
                    <label>
                        パスワード
                        <input type="password" name="password" placeholder="パスワードを入力" class="post-item">
                        <p><?php echo $errorMessages['password'];?></p>
                    </label>
                </div>
                <div class="button-container-withLink">
                    <p class="link-message">
                        <a href="user_register.php">新規登録はこちらから</a>
                    </p>
                    <div class="button-container">
                        <a href="index.php" class="white-button">
                            <span class="material-symbols-outlined">undo</span>
                            レシピ一覧に戻る
                        </a>
                        <button type="submit" class="main-button">
                            <span class="material-symbols-outlined">login</span>
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

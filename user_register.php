<?php
require_once "./db_connect.php";

//セッション開始
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
    if(empty($_POST['user_id'])){
        $errorMessages['user_id'] = 'idは必須です';
    }
    if(empty($_POST['password'])){
        $errorMessages['password'] = 'passwordは必須です';
    }

    if(empty(array_filter($errorMessages))) {
        //IDの重複チェック
        $user_id = $_POST['user_id'];
        $checkSql = "SELECT COUNT(*) FROM users WHERE user_id = :user_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue('user_id', $user_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $userExists = $checkStmt->fetchColumn();

        if($userExists){
            $errorMessages['user_id'] = 'このIDはすでに使用されています';
        } else {
            //登録
            if(isset($_POST['user_id'],$_POST['password'])) {
                //postデータを変数に
                $user_id = $_POST['user_id'];
                //ハッシュ化して変数に
                $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

                $sql = "INSERT INTO users (user_id,password,theme_id) VALUES(:user_id,:password,1)";
                
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(':password', $password_hash, PDO::PARAM_STR);

                    if($stmt->execute()){
                        $resultMessage = '登録が完了しました';
                        $_SESSION['resultMessage'] = '登録が完了しました';
                        header("Location:login.php");
                        exit();
                    } else {
                        $resultMessage = '登録に失敗しました。';
                    }
                } catch(PDOException $e) {
                    $resultMessage = 'sqlエラー:'.$e->getMessage();
                }
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
    <title>ユーザー登録画面</title>
    <link rel="icon" href="img/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0" />
</head>
<body>
    <header>
        <a href="index.php" title="レシピ一覧に戻る">
            <h1 class="app-name">
                <img src="img/logo.png">
            </h1>
        </a>
    </header>
    
    <main id="userRegister-main">
        <div id="userRegister-container">
            <div id="page-name-section">
                <h1>ユーザー登録</h1>
            </div>

            <form action="user_register.php" method="POST">
                <div class="post-item-container">
                    <label>
                        ID(数字)<span class="require">必須</span>
                        <input type="text" name="user_id" placeholder="利用したいIDを入力" class="post-item" require>
                        <p class="error-message"><?php echo $errorMessages['user_id'];?></p>
                    </label>
                </div>
                <div class="post-item-container">
                    <label>
                        パスワード<span class="require">必須</span>
                        <input type="password" name="password" placeholder="パスワードを入力" class="post-item" require>
                        <p class="error-message"><?php echo $errorMessages['password'];?></p>
                    </label>
                </div>
                <div class="button-container">
                    <button type="submit" class="main-button">
                        <span class="material-symbols-outlined">person_add</span>
                        登録
                    </button>
                    <a href="login.php" class="white-button">
                        <span class="material-symbols-outlined">undo</span>
                        ログイン画面に戻る
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <h1 class="app-name">
            <img src="img/logo.png">
        </h1>
        <p>2024秋ハッカソン - グループG</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

<?php

//ユーザ名
$user = 'root';
//パスワード
$password = '';
//データベース名
$database = 'hakkason_gteam_2';
//サーバ名
$server = 'localhost:3308';

//DSN文字列
$dsn = "mysql:host={$server};dbname={$database};charset=utf8";

//mysqlへの接続
try {
    $pdo =  new PDO($dsn, $user, $password);
    
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, false);

    //例外
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error: ' . $e -> getMessage();
    die();
}

?>
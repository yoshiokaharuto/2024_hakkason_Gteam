<?php

session_start();

//sessionの変数をクリア
$_SESSION = [];

// セッションのキー(クッキー)を消去する
if(isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 1800);
}

//セッションを破棄
session_destroy();

//login.phpにリダイレクト
header("Location:index.php");
?>
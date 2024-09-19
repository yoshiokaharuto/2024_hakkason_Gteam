<?php

session_start();

//sessionの変数をクリア
$_SESSION = [];

//セッションを破棄
session_destroy();

//login.phpにリダイレクト
header("Location:login.php");
?>
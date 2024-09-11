<?php
// データベース接続ファイルを読み込む
require_once 'db_connect.php';

// POSTデータがすべて存在するか確認
if (isset($_POST['name']) && isset($_POST['genre']) && isset($_POST['ingredient']) && isset($_POST['time']) && isset($_POST['process'])) {
    // POSTデータを変数に格納
    $name = $_POST['name'];
    $genre = $_POST['genre'];
    $ingredient = $_POST['ingredient'];
    $time = $_POST['time'];
    $process = $_POST['process'];
    $note = isset($_POST['note']) ? $_POST['note'] : '';

    // SQL文を作成（プレースホルダーを使用）
    $sql = "INSERT INTO recipes (name, genre, ingredient, time, process, note) 
            VALUES (:name, :genre, :ingredient, :time, :process, :note)";

    // SQL文をプリペアして実行
    try {
        $stm = $pdo->prepare($sql);
        // プレースホルダーに値をバインド
        $stm->bindValue(':name', $name, PDO::PARAM_STR);
        $stm->bindValue(':genre', $genre, PDO::PARAM_INT);
        $stm->bindValue(':ingredient', $ingredient, PDO::PARAM_STR);
        $stm->bindValue(':time', $time, PDO::PARAM_INT);
        $stm->bindValue(':process', $process, PDO::PARAM_STR);
        $stm->bindValue(':note', $note, PDO::PARAM_STR);

        // SQL文を実行してデータベースにデータを挿入
        if ($stm->execute()) {
            echo "レシピが正常に投稿されました！";
        } else {
            echo "レシピの投稿に失敗しました。";
        }
    } catch (PDOException $e) {
        // SQLエラー発生時の処理
        echo "SQLエラー: " . $e->getMessage();
    }
} else {
    // 必須項目が不足している場合のメッセージ
    echo "全ての必須項目を入力してください。";
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシピ投稿フォーム</title>
</head>
<body>
    <h1>レシピ投稿フォーム</h1>
    <form action="index.php" method="POST">
        <div>
            <label>レシピ名</label><br>
            <input type="text" name="name" required>
        </div>
        <div>
            <label>ジャンル</label><br>
            <select name="genre" required>
                <option value="1">和風</option>
                <option value="2">洋風</option>
                <option value="3">中華風</option>
                <option value="4">お菓子・デザート</option>
            </select>
        </div>
        <div>
            <label>材料</label><br>
            <textarea name="ingredient" required></textarea>
        </div>
        <div>
            <label>所要時間（分）</label><br>
            <input type="number" name="time" required>
        </div>
        <div>
            <label>手順</label><br>
            <textarea name="process" required></textarea>
        </div>
        <div>
            <label>メモ</label><br>
            <textarea name="note"></textarea>
        </div>
        <input type="submit" value="投稿">
    </form>
</body>
</html>

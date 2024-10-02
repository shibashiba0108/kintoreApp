<?php

// データベース接続情報
$host = 'localhost';
$dbname = 'user_db';
$user = 'user_user';
$pass = 'user_pass';

// 管理者情報
$adminName = 'admin';
$adminEmail = 'admin@example.com';
$adminPassword = 'adminpass'; // プレーンテキストのパスワード
$adminRole = 'admin';

try {
    // データベースに接続
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // パスワードをハッシュ化
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    // 管理者情報をデータベースにインサートするSQLクエリ
    $sql = "INSERT INTO admins (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);

    // クエリ実行
    $stmt->execute([
        ':name' => $adminName,
        ':email' => $adminEmail,
        ':password' => $hashedPassword,
        ':role' => $adminRole
    ]);

    echo "管理者情報が正常にインサートされました。";

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}

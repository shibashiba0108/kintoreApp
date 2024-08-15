<?php

namespace kintore;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use PDO;
use Exception;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db); // $sesオブジェクトの初期化

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

// データベース接続
try {
    $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
    $pdo = $db->getPDO();
    if (!$pdo) {
        throw new Exception('PDOオブジェクトが正しく初期化されていません。');
    }
} catch (Exception $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

// 検索キーワードを取得
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
//var_dump($keyword);
if (empty($keyword)) {
    die('検索キーワードが空です。');
}

// SQLクエリの準備
$sql = "SELECT  
            exercise.excs_name, 
            exercise.id,
            body_part.bdp_name 
        FROM 
            exercise 
        JOIN 
            body_part 
        ON 
            exercise.bdp_id = body_part.id 
        WHERE 
            exercise.excs_name LIKE :keyword 
        OR 
            body_part.bdp_name LIKE :keyword";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
    $stmt->execute();

    // データの取得
    $dataArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('クエリエラー: ' . $e->getMessage());
}

// テンプレートにデータを渡す
$context = [
    'user_id' => $_SESSION['user_id'], // ユーザーIDをセッションから取得
    'dataArr' => $dataArr,
    'keyword' => $keyword
];

echo $twig->render('search.twig',$context);
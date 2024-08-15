<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Performance;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$performance = new Performance($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

$search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';

// デフォルトの検索結果を取得
$dataArr = $performance->searchPerformanceData($userId, $search_date, $search_name);

echo $twig->render('record.twig', ['dataArr' => $dataArr, 'search_date' => $search_date, 'search_name' => $search_name]);

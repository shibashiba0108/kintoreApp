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
    'cache' => Bootstrap::CACHE_DIR,
]);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $deleteId = $_POST['delete_id'];

  // トレーニングデータを削除する処理
  $result = $performance->delPerformanceData($deleteId);

  if ($result) {
      $context['message'] = "トレーニングが削除されました。";
  } else {
      $context['error'] = "トレーニングの削除に失敗しました。";
  }
}

$perPage = 5; // 1ページに表示する日数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// 全てのトレーニングデータを取得
$allDataArr = $performance->getPerformanceData($userId); // 既存のメソッドを活用

// データを日付ごとにグループ化
$groupedData = [];
foreach ($allDataArr as $row) {
    $date = $row['pfmc_date'];  // 'pfmc_date' でグループ化
    if (!isset($groupedData[$date])) {
        $groupedData[$date] = [];
    }
    $groupedData[$date][] = $row;
}

// 直近5日分のデータを取得（日付順に取得）
$recentData = array_slice($groupedData, 0, 5, true);

// 古いデータを取得
$olderData = array_slice($groupedData, $offset, $perPage, true);

// 合計ページ数を計算
$totalPages = ceil(count($groupedData) / $perPage);

$context['recentData'] = $recentData;
$context['olderData'] = $olderData;
$context['currentPage'] = $page;
$context['totalPages'] = $totalPages;

// 月別サマリーを取得
$monthlySummary = $performance->getMonthlyPerformanceSummary($userId);
$context['monthlySummary'] = $monthlySummary;

echo $twig->render('training_history.twig', $context);
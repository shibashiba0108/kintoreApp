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
    'cache' => false
]);

$ses->checkSession(); // セッションのチェック

if (!isset($_SESSION['user_id'])) {
    error_log('User ID is not set in session. Redirecting to login.');
    header('Location: /DT/kintore/contents/login_register.php');
    exit;
}

$userId = $_SESSION['user_id'];

$excs_id = isset($_POST['excs_id']) && preg_match('/^\d+$/', $_POST['excs_id']) ? $_POST['excs_id'] : null;
$pfmc_id = isset($_GET['id']) && preg_match('/^\d+$/', $_GET['id']) ? $_GET['id'] : null;

$context = [
    'dataArr' => [],
    'weight' => '',
    'reps' => '',
    'sets' => '',
    'duration' => '',
    'date' => ''
];

// フォームデータが正しく送信されているか確認
if ($excs_id !== null && isset($_POST['weight'], $_POST['reps'], $_POST['sets'], $_POST['duration'], $_POST['date'])) {
    $weight = $_POST['weight'];
    $reps = $_POST['reps'];
    $sets = $_POST['sets'];
    $duration = $_POST['duration'];
    $date = $_POST['date'];

    error_log("User ID: $userId, Exercise ID: $excs_id, Weight: $weight, Reps: $reps, Sets: $sets, Duration: $duration, Date: $date");

    $res = $performance->insPerformanceData($userId, $excs_id, $weight, $reps, $sets, $duration, $date);

    if ($res === false) {
        error_log('記録に失敗しました。');
        exit('記録に失敗しました。');
    } else {
        error_log('記録が成功しました。');
        // 記録が成功したらリダイレクト
        header('Location: /DT/kintore/contents/performance.php');
        exit();
    }
}

// パフォーマンスデータの削除処理
if ($pfmc_id !== null) {
    error_log("Deleting performance data with ID: $pfmc_id");
    $res = $performance->delPerformanceData($pfmc_id);
    if ($res === false) {
        error_log('削除に失敗しました。');
        exit('削除に失敗しました。');
    } else {
        error_log('削除が成功しました。');
        header('Location: ' . Bootstrap::ENTRY_URL . 'performance.php'); // リダイレクトして再送信を防ぐ
        exit();
    }
}

// 本日のトレーニング記録のみを取得
$context['dataArr'] = $performance->getTodayPerformanceData($userId);

echo $twig->render('performance.twig', $context);
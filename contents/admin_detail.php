<?php

namespace kintore\admin;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Performance;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$performance = new Performance($db);

// 管理者のセッションをチェック
$ses->checkAdminSession();

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$userId = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pfmcId = $_POST['pfmc_id'] ?? 0;

    if ($action === 'delete' && $pfmcId > 0) {
        // トレーニング履歴の削除処理
        $performance->deletePerformanceDataForAdmin($pfmcId);
    }

    header("Location: /DT/kintore/contents/admin_detail.php?id={$userId}");
    exit;
}

// 対象ユーザーのトレーニング履歴を取得
$trainingHistory = $performance->getPerformanceData($userId);

echo $twig->render('admin_detail.twig', [
    'trainingHistory' => $trainingHistory,
    'userId' => $userId
]);
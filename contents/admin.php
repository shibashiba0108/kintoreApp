<?php

namespace kintore\admin;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);

// Basic認証
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo '認証が必要です';
    exit;
}

// 管理者のセッションをチェック
$ses->checkAdminSession();

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

// ユーザー一覧の一部を取得
$users = $db->selectRaw("SELECT id, user_name, email FROM users LIMIT 5");

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'delete') {
        $userId = $_POST['user_id'] ?? $_GET['user_id'] ?? 0;

        if ($userId > 0) {
            $db->delete('users', 'id = ?', [$userId]);
        }
        header('Location: /DT/kintore/contents/admin.php?action=list');
        exit; 
    }
}

echo $twig->render('admin_dashboard.twig', [
    'users' => $users,
]);
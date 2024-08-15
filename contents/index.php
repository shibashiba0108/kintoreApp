<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Auth;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$ses = new Session($db);

$auth = new Auth($db, $ses);

// 過去7日間の登録数を取得
$recentRegistrationsCount = $auth->getRecentRegistrations();

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR,
]);

echo $twig->render('top_page.twig', ['recentRegistrationsCount' => $recentRegistrationsCount]);
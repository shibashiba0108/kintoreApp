<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

echo $twig->render('reset_success.twig');

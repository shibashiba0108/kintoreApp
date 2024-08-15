<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\class\PDODatabase;
use kintore\class\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);

$ses->checkSession();
$ses->logout();

header('Location: /DT/kintore/contents/login_register.php'); // ログインページにリダイレクト
exit;



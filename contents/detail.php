<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Exercise;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$exe = new Exercise($db);

$loader =  new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader,[
  'cache' => Bootstrap::CACHE_DIR,
]);

$ses->checkSession(); // セッションのチェック

if (!isset($_SESSION['user_id'])) {
  error_log('User ID is not set in session. Redirecting to login.');
  header('Location: /DT/kintore/contents/login_register.php');
  exit;
}

$userId = $_SESSION['user_id'];

$id = (isset($_GET['id']) === true && preg_match('/^\d+$/', $_GET['id']) === 1) ? $_GET['id'] : '';
//var_dump($id);
//$idがないときはlist.phpにリダイレクトされる
if ($id === ''){
    header('Location: /DT/kintore/contents/list.php');
}
//
$bodyArr = $exe->getBodyPartList();
//var_dump($bodyArr);
//
$exerciseData = $exe->getExerciseDetailData($id);
$context['bodyArr'] = $bodyArr;
$context['exerciseData'] = $exerciseData[0];//連想配列

echo $twig->render('detail.twig',$context);
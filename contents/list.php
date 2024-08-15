<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Exercise;
use kintore\class\Performance;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$exe = new Exercise($db);
$performance = new Performance($db);

$loader =  new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader,[
  'cache' => Bootstrap::CACHE_DIR,
]);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

if (isset($_SESSION['user_id'])) {
        $context['user_id'] = $_SESSION['user_id'];
        $_SESSION['login_ok'] = true;
} else {
        header('Location: /DT/kintore/contents/login_register.php');
        exit;
}

$bdp_id = (isset($_GET['id']) && preg_match('/^[0-9]+$/', $_GET['id'])) ? $_GET['id'] : '';

$bodyArr = $exe->getBodyPartList();
$context['dataArr'] = $exe->getExerciseList($bdp_id);
$context['bodyArr'] = $bodyArr;
error_log('Body Parts: ' . print_r($bodyArr, true));

echo $twig->render('list.twig',$context);

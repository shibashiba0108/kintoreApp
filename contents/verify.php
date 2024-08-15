<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Auth;

$error = '';
$message = '';

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$auth = new Auth($db, $ses);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

if (isset($_GET['code'])) {
    $verificationCode = $_GET['code'];
    $result = $auth->verifyEmail($verificationCode);

    if ($result['success']) {
        $_SESSION['user_id'] = $result['user_id'];  // 主キーのidをセッションに保存
        // リダイレクト先をprofile_register.phpに設定
        header('Location: /DT/kintore/contents/profile_register.php');
        exit;
    } else {
        $error = $result['message'];
    }
} else {
    $error = '検証コードが見つかりません。';
}

echo $error;


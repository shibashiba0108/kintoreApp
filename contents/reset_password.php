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

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$code = $_GET['code'] ?? '';

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$auth = new Auth($db, $ses);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['password'] ?? '';

    if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $newPassword)) {
        $user = $auth->verifyResetCode($code);

        if ($user) {
            $auth->updatePassword($code, $newPassword);
            header('Location: /DT/kintore/contents/reset_complete.php');
            exit;
        } else {
            echo "無効なリセットコードです。";
        }
    } else {
        echo "パスワードは8文字以上で、大文字・小文字・数字をそれぞれ1文字以上含む必要があります。";
    }
}

echo $twig->render('reset_password.twig', ['code' => $code]);

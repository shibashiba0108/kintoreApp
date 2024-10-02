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

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR );
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $auth->loginAdmin($email, $password);
    if ($result['success']) {
        $_SESSION['admin_id'] = $result['admin_id'];
        header('Location: /DT/kintore/contents/admin.php');
        exit; // ここでリダイレクトしているので、後続のコードには到達しません。
    } else {
        $error = $result['message'];
    }
}

echo $twig->render('admin_login.twig', ['error' => $error ?? '']);
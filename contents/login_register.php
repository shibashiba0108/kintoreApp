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

$error = '';
$message = '';

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$auth = new Auth($db, $ses);

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userIdentifier = $_POST['user_name'] ?? $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $recaptchaResult = $auth->verifyRecaptcha($recaptchaResponse);
    if (!$recaptchaResult['success']) {
        $error = $recaptchaResult['message'];
    } else {
        if ($action == 'register') {
            $result = $auth->register($userIdentifier, $userIdentifier, $password);
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id']; // 'user_name' ではなく 'user_id' を使用
                $ses->updateSessionUserId($result['user_id']); // 'updateSessionUserId' を使用
                header('Location: /DT/kintore/contents/register_success.php');
                exit;
            } else {
                $error = $result['message'];
            }
        } elseif ($action == 'login') {
            $result = $auth->login($userIdentifier, $password);
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id']; // 'user_name' ではなく 'user_id' を使用
                $ses->updateSessionUserId($result['user_id']); // 'updateSessionUserId' を使用
                header('Location: /DT/kintore/contents/mypage.php');
                exit;
            } else {
                $error = $result['message'];
            }
        } elseif ($action == 'google_login') {
            $result = $auth->googleLogin($userIdentifier, $password);
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id']; // 'user_name' ではなく 'user_id' を使用
                $ses->updateSessionUserId($result['user_id']); // 'updateSessionUserId' を使用
                header('Location: /DT/kintore/contents/register_success.php');
                exit;
            } else {
                $error = $result['message'];
            }
        } elseif ($action == 'reset_password') {
            $result = $auth->resetPassword($userIdentifier);
            if ($result['success']) {
                header('Location: /DT/kintore/contents/reset_success.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }

    if ($error) {
        error_log("Login failed: " . $error);
    }
}

// Handle Google Login
if (isset($_GET['code'])) {
    $result = $auth->handleGoogleLogin($_GET['code']);
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user_id']; // 'user_name' ではなく 'user_id' を使用
        $ses->updateSessionUserId($result['user_id']); // 'updateSessionUserId' を使用
        header('Location: /DT/kintore/contents/mypage.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

echo $twig->render('login_register.twig', ['error' => $error, 'message' => $message]);

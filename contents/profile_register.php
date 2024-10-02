<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';
require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Profile;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$error = '';
$message = '';

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$profile = new Profile($db);

$loader = new FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

$ses->checkSession(); // セッションのチェック

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_SESSION['user_id']);
    $nickname = $_POST['nickname'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $profileImage = $_FILES['profile_image']['name'];

    if ($profileImage) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // ファイル名のサニタイズ
        $safeFileName = basename(preg_replace("/[^A-Za-z0-9.]/", "_", $profileImage));
        $targetFile = $targetDir . $safeFileName;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $error = 'ファイルのアップロードに失敗しました。';
        }
    }

    if (!$error) {
        try {
            $profile->createUserProfile($userId, $nickname, $height, $weight, $birthdate, $gender, $profileImage);
            header('Location: /DT/kintore/contents/mypage.php');
            exit;
        } catch (\Exception $e) { 
            $error = $e->getMessage();
        }
    }
}

// 現在のユーザープロフィールを取得
$currentProfile = $profile->getUserProfile($userId);

echo $twig->render('profile_register.twig', ['error' => $error, 'message' => $message, 'profile' => $currentProfile]);
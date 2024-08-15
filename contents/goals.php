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
use Exception;

$error = '';
$message = '';
$alertMessage = '';

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
    $fitnessGoalId = intval($_POST['goal']);

    // 初期化
    $targetWeight = null;
    $targetDate = null;
    $exerciseName = null;
    $exerciseWeight = null;

    // フィットネスゴールに応じてターゲットの値を取得
    switch ($fitnessGoalId) {
        case 1: // 減量
            $targetWeight = $_POST['target_weight'];
            $targetDate = $_POST['target_date'];
            break;
        case 2: // 筋肥大
            $targetWeight = $_POST['target_weight_muscle'];
            $targetDate = $_POST['target_date_muscle'];
            break;
        case 3: // 筋力向上
            $exerciseName = $_POST['exercise_name'];
            $exerciseWeight = $_POST['exercise_weight'];
            break;
    }

    // Profile クラスのプロパティに値を設定
    $profile->setTargetWeight($targetWeight);
    $profile->setTargetDate($targetDate);
    $profile->setExerciseName($exerciseName);
    $profile->setExerciseWeight($exerciseWeight);

    error_log("Setting goal for user_id: " . $userId . ", fitness_goal_id: " . $fitnessGoalId);

    try {
        // ユーザーのゴールを設定
        $profile->setUserGoal($userId, $fitnessGoalId);
        $alertMessage = 'フィットネスゴールが設定されました。';

    } catch (Exception $e) {
        $alertMessage = $e->getMessage();
    }
}

// 現在のゴールを取得
$currentGoalId = $profile->getCurrentGoal($userId);
$currentGoalDetails = $profile->getCurrentGoalDetails($userId);

// フィットネスゴールの説明を取得
$goals = $profile->getFitnessGoals();

echo $twig->render('goals.twig', ['currentGoalId' => $currentGoalId, 'goals' => $goals, 'alertMessage' => $alertMessage, 'currentGoalDetails' => $currentGoalDetails]);
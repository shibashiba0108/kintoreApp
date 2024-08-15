<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Session;
use kintore\class\Performance;
use kintore\class\Profile;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ses = new Session($db);
$performance = new Performance($db);
$profile = new Profile($db);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR,
]);

$ses->checkSession();
$userId = $_SESSION['user_id'];

$context = [];
$context['user_id'] = $userId;

// ユーザーのニックネームを取得
$stmt = $db->prepare("SELECT nickname FROM user_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$userProfile = $stmt->fetch(\PDO::FETCH_ASSOC);
$context['nickname'] = $userProfile['nickname'] ?? '';

// ユーザーのフィットネスゴールを取得
$currentGoalId = $profile->getCurrentGoal($userId);
$context['current_goal_id'] = $currentGoalId;

// 直近1日のトレーニングデータを取得
$lastPerformance = $performance->getLastPerformanceData($userId);
$context['last_performance'] = $lastPerformance;

// お薦めトレーニングを取得
$recommendedExercises = $performance->getRecommendedExercises($userId, $currentGoalId);
$context['recommended_exercises'] = $recommendedExercises;

echo $twig->render('mypage.twig', $context);


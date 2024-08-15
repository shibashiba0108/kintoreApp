<?php

namespace kintore\contents;

require_once dirname(__FILE__) . '/Bootstrap.class.php';

use kintore\contents\Bootstrap;
use kintore\class\PDODatabase;
use kintore\class\Exercise;
use kintore\class\Performance;
use kintore\class\Session;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$exercise = new Exercise($db);
$performance = new Performance($db);
$ses = new Session($db);

$ses->checkSession(); // セッションのチェック

// セッションからユーザーIDを取得
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $excs_name = $_POST['excs_name'];
    $date = $_POST['date'];
    $weight = $_POST['weight'];
    $reps = $_POST['reps'];
    $sets = $_POST['sets'];
    $duration = $_POST['duration'];
    $bdp_id = $_POST['bdp_id'];

    // 新しい種目をデータベースに追加
    $new_exercise_id = $exercise->addExercise($excs_name, $bdp_id);

    if ($new_exercise_id) {
        // トレーニング記録を追加
        $res = $performance->insPerformanceData($userId, $new_exercise_id, $weight, $reps, $sets, $duration, $date);
        if ($res) {
            header('Location: ' . Bootstrap::ENTRY_URL . 'performance.php');
            exit;
        } else {
            echo 'トレーニング記録に失敗しました。';
        }
    } else {
        echo '種目の追加に失敗しました。';
    }
}

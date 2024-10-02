<?php

namespace kintore\class;

class Performance
{
    private $db = null;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    // 管理者用: 特定のユーザーIDの全トレーニング履歴を取得
    public function getAllPerformanceDataForAdmin($userId = null)
    {
        $table = 'performance p LEFT JOIN exercise e ON p.excs_id = e.id';
        $column = 'p.id, p.user_id, e.excs_name, p.weight_count, p.rep_count, p.set_count, p.duration, p.pfmc_date';
        $where = 'p.delete_flg = 0';
        $arrVal = [];

        // 特定のユーザーの履歴を表示したい場合
        if ($userId !== null) {
            $where .= ' AND p.user_id = ?';
            $arrVal[] = $userId;
        }

        $order = 'ORDER BY p.pfmc_date DESC';

        return $this->db->select($table, $column, $where, $arrVal, $order);
    }

    // 管理者用: 特定のトレーニング履歴の編集
    public function updatePerformanceData($pfmcId, $excs_id, $weight, $reps, $sets, $duration, $date)
    {
        $table = 'performance';
        $data = [
            'excs_id' => $excs_id,
            'weight_count' => $weight,
            'rep_count' => $reps,
            'set_count' => $sets,
            'duration' => $duration,
            'pfmc_date' => $date
        ];
        $where = 'id = ?';
        $params = [$pfmcId];

        $this->db->update($table, $data, $where, $params);
    }

    // 管理者用: トレーニング履歴の論理削除
    public function deletePerformanceDataForAdmin($pfmcId)
    {
        $table = 'performance';
        $data = ['delete_flg' => 1];
        $where = 'id = ?';
        $params = [$pfmcId];

        $this->db->update($table, $data, $where, $params);
    }

    public function insPerformanceData($userId, $excs_id, $weight, $reps, $sets, $duration, $date)
    {
        $table = 'performance';
        $insData = [
            'user_id' => $userId,
            'excs_id' => $excs_id,
            'weight_count' => $weight,
            'rep_count' => $reps,
            'set_count' => $sets,
            'duration' => $duration,
            'pfmc_date' => $date
        ];

        $res = $this->db->insert($table, $insData);

        // デバッグ用に挿入後のデータをログ出力
        if ($res) {
            error_log("Data inserted successfully: " . print_r($insData, true));
        } else {
            error_log("Data insert failed: " . print_r($insData, true));
            error_log("SQL Error: " . $this->db->errorInfo()[2]);
        }

        return $res;
    }

    public function getPerformanceData($userId)
    {
        $table = 'performance p LEFT JOIN exercise e ON p.excs_id = e.id';
        $column = 'p.id, p.user_id, e.excs_name, p.weight_count, p.rep_count, p.set_count, p.duration, p.pfmc_date';
        $where = 'p.user_id = ? AND p.delete_flg = ?';
        $arrVal = [$userId, 0];
        $order = 'ORDER BY p.pfmc_date DESC';

        return $this->db->select($table, $column, $where, $arrVal, $order);
    }

    public function searchPerformanceData($userId, $search_date, $search_name)
    {
        $table = 'performance p LEFT JOIN exercise e ON p.excs_id = e.id';
        $column = 'p.id, p.user_id, e.id, p.weight_count, p.rep_count, p.set_count, p.duration, p.pfmc_date';
        $where = 'p.user_id = ? AND p.delete_flg = 0';
        $arrVal = [$userId];

        if (!empty($search_date)) {
            $where .= ' AND p.pfmc_date = ?';
            $arrVal[] = $search_date;
        }

        if (!empty($search_name)) {
            $where .= ' AND e.excs_name LIKE ?';
            $arrVal[] = '%' . $search_name . '%';
        }

        $order = 'ORDER BY p.pfmc_date DESC'; // 新しい順にソート

        return $this->db->select($table, $column, $where, $arrVal, $order);
    }

    public function getDailyPerformanceData($userId)
    {
        $today = date('Y-m-d'); // 今日の日付を取得

        $sql = "SELECT p.id, p.user_id, e.excs_name, e.id AS exercise_id, p.weight_count, p.rep_count, p.set_count, p.duration, p.pfmc_date
        FROM performance p
        LEFT JOIN exercise e ON p.excs_id = e.id
        WHERE p.user_id = ? AND DATE(p.pfmc_date) = ? AND p.delete_flg = 0
        ORDER BY p.pfmc_date DESC";

        $params = [$userId, $today];
        $data = $this->db->selectRaw($sql, $params);

        error_log("getDailyPerformanceData - SQL: $sql");
        error_log("getDailyPerformanceData - Params: " . print_r($params, true));
        error_log("getDailyPerformanceData - Retrieved Data: " . print_r($data, true));

        return $data;
    }

    public function getMonthlyPerformanceSummary($userId)
    {
        $sql = "SELECT DATE_FORMAT(p.pfmc_date, '%Y-%m') as month, COUNT(DISTINCT DATE(p.pfmc_date)) as count
            FROM performance p
            WHERE p.user_id = ? AND p.delete_flg = 0
            GROUP BY month
            ORDER BY month DESC";

        $params = [$userId];

        // 生のSQLクエリを実行するために selectRaw メソッドを使用
        $data = $this->db->selectRaw($sql, $params);

        // デバッグ用に取得したデータをログ出力
        error_log("Monthly summary retrieved: " . print_r($data, true));

        return $data;
    }

    public function delPerformanceData($pfmc_id)
    {
        $table = 'performance';
        $insData = ['delete_flg' => 1];  // delete_flg を 1 に設定
        $where = 'id = ?';
        $arrWhereVal = [$pfmc_id];

        // SQLの実行
        $res = $this->db->update($table, $insData, $where, $arrWhereVal);

        // エラーチェック
        if ($res === false) {
            error_log("Failed to delete performance data for ID: $pfmc_id");
            error_log("SQL Error: " . print_r($this->db->errorInfo(), true));
        } else {
            error_log("Data logically deleted successfully for ID: $pfmc_id");
        }

        return $res;
    }

    public function getTodayPerformanceData($userId)
    {
        $today = date('Y-m-d'); // 今日の日付を取得

        $sql = "SELECT p.id, p.user_id, e.excs_name, p.weight_count, p.rep_count, p.set_count, p.duration, p.pfmc_date
            FROM performance p
            LEFT JOIN exercise e ON p.excs_id = e.id
            WHERE p.user_id = ? AND p.delete_flg = 0 AND DATE(p.pfmc_date) = ?
            ORDER BY p.pfmc_date DESC";

        $params = [$userId, $today];
        $data = $this->db->selectRaw($sql, $params);

        error_log("getTodayPerformanceData - SQL: $sql");
        error_log("getTodayPerformanceData - Params: " . print_r($params, true));
        error_log("getTodayPerformanceData - Retrieved Data: " . print_r($data, true));

        return $data;
    }

    public function getLastPerformanceData($userId)
    {
        $sql = "SELECT p.id, p.user_id, e.excs_name, p.set_count, p.rep_count, p.pfmc_date
            FROM performance p
            LEFT JOIN exercise e ON p.excs_id = e.id
            WHERE p.user_id = ? AND p.delete_flg = 0 AND p.pfmc_date = (
                SELECT MAX(pfmc_date)
                FROM performance
                WHERE user_id = ?
            )
            ORDER BY p.pfmc_date DESC";

        $params = [$userId, $userId];  // クエリの両方で同じ userId を使用
        return $this->db->selectRaw($sql, $params);
    }

    public function getRecommendedExercises($userId, $fitness_goal_id)
    {
        $recommendations = [];

        // フィットネスゴールに基づいたお薦めトレーニングのロジック
        switch ($fitness_goal_id) {
            case 1: // 減量
                $recommendations = [
                    ['exercise' => 'Running', 'sets' => 1, 'reps' => '30 minutes'],
                    ['exercise' => 'Jump Rope', 'sets' => 3, 'reps' => '100 reps']
                ];
                break;
            case 2: // 筋肥大
                $recommendations = [
                    ['exercise' => 'Bench Press', 'sets' => 4, 'reps' => 8],
                    ['exercise' => 'Squats', 'sets' => 4, 'reps' => 10]
                ];
                break;
            case 3: // 筋力向上
                $recommendations = [
                    ['exercise' => 'Deadlift', 'sets' => 5, 'reps' => 5],
                    ['exercise' => 'Overhead Press', 'sets' => 5, 'reps' => 5]
                ];
                break;
            case 4: // 健康維持
                $recommendations = [
                    ['exercise' => 'Walking', 'sets' => 1, 'reps' => '30 minutes'],
                    ['exercise' => 'Yoga', 'sets' => 1, 'reps' => '60 minutes']
                ];
                break;
        }

        return $recommendations;
    }
}
<?php

namespace kintore\class;

class Profile
{
    private $db;
    private $targetWeight = null;
    private $targetDate = null;
    private $exerciseName = null;
    private $exerciseWeight = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createUserProfile($userId, $nickname, $height, $weight, $birthdate, $gender, $profileImage)
    {
        $data = [
            'user_id' => $userId,
            'nickname' => $nickname,
            'height' => $height,
            'weight' => $weight,
            'birthdate' => $birthdate,
            'gender' => $gender,
            'profile_image' => $profileImage
        ];

        return $this->db->insert('user_profiles', $data);
    }

    public function getUserProfile($userId) {
        $sql = 'SELECT * FROM user_profiles WHERE user_id = :user_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function setUserGoal($userId, $fitnessGoalId)
    {
        // 既存の目標を削除
        $deleteQuery = "DELETE FROM user_goals WHERE user_id = ?";
        $stmt = $this->db->prepare($deleteQuery);
        $stmt->execute([$userId]);

        $data = [
            'user_id' => $userId,
            'fitness_goal_id' => $fitnessGoalId,
            'target_weight' => $this->targetWeight,
            'target_date' => $this->targetDate,
            'exercise_name' => $this->exerciseName,
            'exercise_weight' => $this->exerciseWeight
        ];

        // デバッグログ追加
        error_log("Inserting user goal: " . json_encode($data));

        return $this->db->insert('user_goals', $data);
    }

    public function setTargetWeight($weight)
    {
        $this->targetWeight = $weight;
    }

    public function setTargetDate($date)
    {
        $this->targetDate = $date;
    }

    public function setExerciseName($name)
    {
        $this->exerciseName = $name;
    }

    public function setExerciseWeight($weight)
    {
        $this->exerciseWeight = $weight;
    }

    public function getCurrentGoal($userId)
    {
        $query = "SELECT fitness_goal_id FROM user_goals WHERE user_id = ?";
        $result = $this->db->selectRaw($query, [$userId]);
        return $result[0]['fitness_goal_id'] ?? '';
    }

    public function getFitnessGoals()
    {
        $query = "SELECT id, goal_name, description FROM fitness_goals";
        return $this->db->selectRaw($query);
    }

    public function getCurrentGoalDetails($userId)
    {
        $query = "SELECT fitness_goal_id, target_weight, target_date, exercise_name, exercise_weight FROM user_goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getGoalStatus($userId)
    {
        $query = "SELECT fitness_goal_id, target_date, created_at FROM user_goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $goal = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($goal) {
            $remainingDays = null;
            $status = null;

            if ($goal['target_date']) {
                $currentDate = new \DateTime();
                $targetDate = new \DateTime($goal['target_date']);
                $interval = $currentDate->diff($targetDate);
                $remainingDays = $interval->days;

                // 残り日数が0以下であれば達成状況を「達成済み」にする
                $status = $interval->invert ? '達成済み' : '未達成';
            }

            return [
                'remaining_days' => $remainingDays,
                'status' => $status,
                'target_date' => $goal['target_date'],
            ];
        }

        return null;
    }
}

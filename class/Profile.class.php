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
}

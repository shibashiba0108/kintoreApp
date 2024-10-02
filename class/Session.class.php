<?php

namespace kintore\class;

class Session
{
    public $session_key = '';
    public $db = null;

    public function __construct($db)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->session_key = session_id();
        $this->db = $db;
    }

    public function checkSession()
    {
        $sessionData = $this->selectSession();
        if ($sessionData !== false && strtotime($sessionData['expires_at']) > time()) {
            $_SESSION['user_id'] = $sessionData['user_id']; // 'user_name' を 'user_id' に変更
        } else {
            $this->createSession();
        }
    }

    public function checkAdminSession()
    {
        if (!isset($_SESSION['admin_id'])) {
            // 仮のadmin_idとadmin_roleを設定
            $_SESSION['admin_id'] = 1; // 仮のadmin_id
            $_SESSION['admin_role'] = 'admin'; // 仮のadmin_role

            // セッションを作成
            $this->createAdminSession();
        } else {
            // 既存のセッションがある場合
            $sessionData = $this->selectAdminSession();
            if ($sessionData === false || strtotime($sessionData['expires_at']) <= time()) {
                // セッションが無効または期限切れの場合、再作成
                $this->createAdminSession();
            }
        }
    }

    private function selectSession()
    {
        $table = 'sessions';
        $col = 'user_id, expires_at'; // 'user_name' を 'user_id' に変更
        $where = 'session_key = ?';
        $arrVal = [$this->session_key];

        $res = $this->db->select($table, $col, $where, $arrVal);
        return (count($res) !== 0) ? $res[0] : false;
    }

    private function selectAdminSession()
    {
        $sessionId = session_id();
        $query = "SELECT * FROM admin_sessions WHERE session_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sessionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function createSession()
    {
        $this->deleteOldSession();

        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $table = 'sessions';
        $insData = [
            'session_key' => $this->session_key,
            'expires_at' => $expires_at
        ];

        $this->db->insert($table, $insData);
    }

    private function createAdminSession()
    {
        session_regenerate_id(true);
        $sessionId = session_id();
        $adminId = $_SESSION['admin_id'] ?? null;
        $adminRole = $_SESSION['admin_role'] ?? 'admin';
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 minutes'));

        $query = "INSERT INTO admin_sessions (session_id, admin_id, admin_role, expires_at) VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE admin_id = VALUES(admin_id), admin_role = VALUES(admin_role), expires_at = VALUES(expires_at)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sessionId, $adminId, $adminRole, $expiresAt]);
    }

    private function deleteOldSession()
    {
        $table = 'sessions';
        $where = 'session_key = ?';
        $arrWhereVal = [$this->session_key];

        $this->db->delete($table, $where, $arrWhereVal);
    }

    public function updateSessionUserId($userId) // 'updateSessionUserName' ではなく 'updateSessionUserId' に変更
    {
        $table = 'sessions';
        $data = ['user_id' => $userId]; // 'user_name' を 'user_id' に変更
        $where = 'session_key = ?';
        $arrWhereVal = [$this->session_key];

        $this->db->update($table, $data, $where, $arrWhereVal);
    }

    public function updateAdminSession($adminId)
    {
        $table = 'admin_sessions';
        $data = ['admin_id' => $adminId];
        $where = 'session_key = ?';
        $arrWhereVal = [$this->session_key];

        $this->db->update($table, $data, $where, $arrWhereVal);
    }

    public function logout()
    {
        $_SESSION = array();
        session_destroy();
    }
}

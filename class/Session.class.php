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

    private function selectSession()
    {
        $table = 'sessions';
        $col = 'user_id, expires_at'; // 'user_name' を 'user_id' に変更
        $where = 'session_key = ?';
        $arrVal = [$this->session_key];

        $res = $this->db->select($table, $col, $where, $arrVal);
        return (count($res) !== 0) ? $res[0] : false;
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

    public function logout()
    {
        $_SESSION = array();
        session_destroy();
    }
}

<?php

namespace kintore\class;

class PDODatabase
{
    private $dbh = null;
    private $db_host = '';
    private $db_user = '';
    private $db_pass = '';
    private $db_name = '';
    private $db_type = '';
    private $order = '';
    private $limit = '';
    private $offset = '';
    private $groupby = '';

    public function __construct($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
        $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name, $db_type);
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
        $this->order = '';
        $this->limit = '';
        $this->offset = '';
        $this->groupby = '';
    }

    private function connectDB($db_host, $db_user, $db_pass, $db_name, $db_type)
    {
        try {
            switch ($db_type) {
                case 'mysql':
                    $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
                    $dbh = new \PDO($dsn, $db_user, $db_pass);
                    $dbh->query('SET NAMES utf8');
                    break;

                case 'pgsql':
                    $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . ' port=5432';
                    $dbh = new \PDO($dsn, $db_user, $db_pass);
                    break;
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            exit();
        }

        return $dbh;
    }

    public function setQuery($query = '', $arrVal = [])
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($arrVal);
    }

    public function count($table, $where = '', $arrVal = [])
    {
        $sql = $this->getSql('count', $table, $where);

        $this->sqlLogInfo($sql, $arrVal);
        $stmt = $this->dbh->prepare($sql);

        $res = $stmt->execute($arrVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return intval($result['NUM']);
    }

    public function setOrder($order = '')
    {
        if ($order !== '') {
            $this->order = ' ORDER BY ' . $order;
        }
    }

    public function setLimitOff($limit = '', $offset = '')
    {
        if ($limit !== "") {
            $this->limit = " LIMIT " . $limit;
        }

        if ($offset !== "") {
            $this->offset = " OFFSET " . $offset;
        }
    }

    public function setGroupBy($groupby)
    {
        if ($groupby !== "") {
            $this->groupby = ' GROUP BY ' . $groupby;
        }
    }

    // 通常のSELECTメソッド
    public function select($table, $columns = '*', $where = '', $arrWhereVal = [], $order = '')
    {
        $sql = "SELECT $columns FROM $table";
        if ($where !== '') {
            $sql .= " WHERE $where";
        }
        if ($order !== '') {
            $sql .= " $order";
        }

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($arrWhereVal);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // 生のSQLクエリを実行するメソッド
    public function selectRaw($sql, $arrVal = [])
    {
        $this->sqlLogInfo($sql, $arrVal);

        $stmt = $this->dbh->prepare($sql);

        $res = $stmt->execute($arrVal);
        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        $data = [];

        while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($data, $result);
        }

        return $data;
    }

    // $table = exercise $column = 'excs_id, excs_name, bdp_id' $where = 'bdp_id = ?'
    private function getSql($type, $table, $where = '', $column = '')
    {
        switch ($type) {
            case 'select':
                // $columnKey = 'excs_id, excs_name, bdp_id';
                $columnKey = ($column !== '') ? $column : "*";
                break;

            case 'count':
                $columnKey = 'COUNT(*) AS NUM';
                break;

            default:
                break;
        }

        // $whereSQL = 'WHERE bdp_id = ?';
        $whereSQL = ($where !== '') ? ' WHERE ' . $where : '';
        $other = $this->groupby . " " . $this->order . " " . $this->limit . " " . $this->offset;

        // SELECT excs_id, excs_name, bdp_id FROM exercise WHERE bdp_id = ?;
        $sql = " SELECT " . $columnKey . " FROM " . $table . $whereSQL . $other;
        // SELECT excs_id, excs_name, bdp_id FROM exercise WHERE bdp_id = ?;
        return $sql;
    }

    // $table = session, 
    public function insert($table, $insData = [])
    {
        $insDataKey = [];
        $insDataVal = [];
        $preCnt = [];

        $columns = '';
        $preSt = '';
        //$insData = ['session_key' => $this->session_key];
        foreach ($insData as $col => $val) {
            $insDataKey[] = $col; // session_key
            $insDataVal[] = $val;// 2 ※ 例
            $preCnt[] = '?'; // ?
        }

        // session_key
        $columns = implode(",", $insDataKey);
        // ?
        $preSt = implode(",", $preCnt);

        // INSERT INTO cart (session_key) VALUES (?);
        $sql = " INSERT INTO "
            . $table
            . " ( "
            . $columns
            . " ) VALUES ("
            . $preSt
            . " ) ";

        $this->sqlLogInfo($sql, $insDataVal);

        // INSERT INTO cart (session_key) VALUES (?);
        $stmt = $this->dbh->prepare($sql);
        // INSERT INTO cart (session_key) VALUES (2);
        // trueかfalse(実行出来なかった時)で返る
        $res = $stmt->execute($insDataVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        // true or false
        return $res;
    }

    public function update($table, $insData = [], $where = '', $arrWhereVal = [])
    {
        $arrPreSt = [];
        foreach ($insData as $col => $val) {
            $arrPreSt[] = $col . " = ?";
        }
        $preSt = implode(',', $arrPreSt);
        $sql = "UPDATE "
            . $table
            . " SET "
            . $preSt
            . " WHERE "
            . $where;

        $updateData = array_merge(array_values($insData), $arrWhereVal);
        $this->sqlLogInfo($sql, $updateData);

        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($updateData);

        // 実行後にSQL文と結果をログに出力
        error_log("Executed SQL: $sql");
        error_log("With Data: " . print_r($updateData, true));

        if ($res === false) {
            error_log("Update failed: " . print_r($stmt->errorInfo(), true));
            $this->catchError($stmt->errorInfo());
        } else {
            error_log("Update succeeded for table $table");
        }
        
        return $res;
    }


    public function getLastId()
    {
        return $this->dbh->lastInsertId();
    }

    private function catchError($errArr = [])
    {
        $errMsg = (!empty($errArr[2])) ? $errArr[2] : "";
        die("SQLエラーが発生しました。" . $errArr[2]);
    }

    private function makeLogFile()
    {
        $logDir = dirname(__DIR__) . "/logs";
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777);
        }
        $logPath = $logDir . '/shopping.log';
        if (!file_exists($logPath)) {
            touch($logPath);
        }
        return $logPath;
    }

    private function sqlLogInfo($str, $arrVal = [])
    {
        $logPath = $this->makeLogFile();
        $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", array_map(function ($val) {
            return is_array($val) ? json_encode($val) : $val;
        }, $arrVal)));
        error_log($logData, 3, $logPath);
    }

    public function getPDO()
    {
        return $this->dbh;
    }

    public function prepare($sql)
    {
        return $this->dbh->prepare($sql);
    }

    public function delete($table, $where, $arrWhereVal)
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arrWhereVal);
        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }
        return $res;
    }
}
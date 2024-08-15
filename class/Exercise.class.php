<?php

namespace kintore\class;

class Exercise
{
  public $bodyArr = [];
  public $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function getBodyPartList()
  {
    $table = ' body_part ';
    $col = ' id, bdp_name ';
    $res = $this->db->select($table, $col);
    return $res;
  }

  public function getExerciseList($bdp_id)
  {
    $table = 'exercise';
    $col = 'id, excs_name, bdp_id';
    $where = '';
    $arrVal = [];

    if ($bdp_id !== '') {
      $where = 'bdp_id = ?';
      $arrVal[] = $bdp_id;
    }

    $res = $this->db->select($table, $col, $where, $arrVal);

    return ($res !== false && count($res) !== 0) ? $res : [];
  }

  public function getExerciseDetailData($id)
  {
    $table = ' exercise ';
    $col = ' id, excs_name, bdp_id ';
    $where = ($id !== '') ? ' id = ? ' : '';
    $arrVal = ($id !== '') ? [$id] : [];
    $res = $this->db->select($table, $col, $where, $arrVal);

    // デバッグ用にデータをログ出力
    error_log("Exercise Detail Data: " . print_r($res, true));

    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  public function addExercise($excs_name, $bdp_id)
  {
    $table = 'exercise';
    $data = [
      'excs_name' => $excs_name,
      'bdp_id' => $bdp_id,
    ];
    $res = $this->db->insert($table, $data);

    if ($res) {
      return $this->db->getLastId(); // 挿入された新しい種目のIDを取得
    } else {
      return false;
    }
  }
}
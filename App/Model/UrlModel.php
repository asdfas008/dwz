<?php
/**
 * User: Hufeng
 * Date: 2017/08/29 19:48
 * Desc: 用户Model
 */
namespace App\Model;
use SasPHP\SqlModel;

class UrlModel extends SqlModel{
    protected $dbName = 'url';

    public function __construct(){
        parent::__construct('DB_SERVER');
    }

    function insertData($data){
        $this->dbConn->insert($this->dbName,$data);
        return $this->dbConn->id();
    }

    function editData($where,$data){
        $this->dbConn->update($this->dbName,$data,$where);
    }

    function delData($data){
        $this->dbConn->delete($this->dbName, $data);
    }

    function findData($where,$fields='*'){
        $res = $this->dbConn->get($this->dbName,$fields,$where);
        return $res;
    }

    function queryData($where,$fields='*'){
        $res = $this->dbConn->select($this->dbName,$fields,$where);
        return $res;
    }
    //获取统计数据
    public function getTotalNum($where){
        $res = $this->dbConn->count($this->dbName,$where);
        return $res;
    }
    //sum数据
    public function sumData($where,$param){
        $res = $this->dbConn->sum($this->dbName,$param,$where);
        return $res;
    }
}

<?php
/**
 * User: Hufeng
 * Date: 2015/12/28 11:35
 * Desc: 数据Model基类
 */
namespace SasPHP;
use Medoo\Medoo;

class SqlModel{
    public $dbConn;
    public function __construct($dbCon='DB_MASTER'){
        if($this->dbConn){
           return $this->dbConn;
        }else{
            $confObj = new Config(BASEDIR.'/App/Common/Conf');
            $conf = $confObj->offsetGet('config');
            $config = $conf[$dbCon];
            $this->dbConn = new Medoo([
                'database_type' => 'mysql',
                'database_name' => $config['database'],
                'server' => $config['server'],
                'username' => $config['dbname'],
                'password' => $config['dbpwd'],
                'charset' => $config['charset']
            ]);
            return $this->dbConn;
        }
    }
}

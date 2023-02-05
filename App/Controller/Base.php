<?php
/**
 * User: Hufeng
 * Date: 2017/12/04 17:51
 * Desc: TRADE SERVE端
 */

namespace App\Controller;
use SasPHP\Config;
class Base {
    public $outData = ['code'=>0,'msg'=>'操作成功！'];
    public $param;
    public $redis;
    public $timeNow;
    public function __construct(){
        $this->param = $_POST ? $this->filterData($_POST) : [];
        $this->timeNow = date('Y-m-d H:i:s');
    }
    //过滤post数据
    protected function filterData($varData){
        if (is_array($varData)) {
            $out = array();
            foreach ($varData as $key => $v) {
                $out[$key] = self::filterData($v);
            }
        } else {
            $out= str_replace('&amp;','&',htmlspecialchars(stripslashes(trim($varData)), ENT_QUOTES));
        }
        return $out;
    }
    //验证参数
    protected function regArguments($needParam,&$arguments){
        foreach($needParam as $key=>$val){
            if(!isset($arguments[$key]) || $arguments[$key]===''){
                $this->outData = ['code'=>401,'msg'=>$val['msg']];
            }else{
                $arguments[$key] = $this->formatArguments($arguments[$key],$val['type']);
            }
        }
        return;
    }
    protected function formatArguments($val,$type){
        switch ($type) {
            case 'int':
                return intval($val);break;
            case 'float':
                return round($val, 2);break;
            case 'tel':
                if (!is_numeric($val) || !preg_match('/^1[\d]{10}$/', $val)) {
                    $this->outData = ['code'=>2,'msg'=>'手机号格式不正确'];
                }
                return $val;break;
            default:
                return $val;
        }
    }
    //验证token
    protected function regToken($token,$pstr){
        //web端验证
        if($token != sha1(md5($pstr.APP_SECRET))){
            $this->outData = ['code'=>1,'msg'=>'非法请求'];
        }
        return;
    }
    //curl请求
    protected function httpCurl($url,$param){
        $ch = curl_init();  //初始化
        curl_setopt($ch, CURLOPT_URL, $url); //设置url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //不直接输出,以文件流返回
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); //超时
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不需要验证对方的证书
        curl_setopt($ch, CURLOPT_POST, true);   //开启post参数传递
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param); //post数组参数
        $response = curl_exec($ch); //执行,返回header body
        curl_close($ch);//关闭
        return $response;
    }
    //get ip
    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',
                $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',
                $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',
                $_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',
                $_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }
    //获取配置文件
    protected function getConf($cfile,$key){
        $confObj = new Config(BASEDIR.'/App/Common/Conf');
        $conf = $confObj->offsetGet($cfile);
        return $conf[$key];
    }
    //生成订单号
    protected function buildOrderNo(){
        $timeStr = date('ymdHis');
        $myPid = str_pad(getmypid(), 5, "0", STR_PAD_LEFT);
        $randNum = rand(100,999);
        $orderNo = $timeStr.$myPid.$randNum;
        return $orderNo;
    }
    //当前毫秒
    protected function mstime(){
        list($msec, $sec) = explode(' ', microtime());
        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }
    protected function fjson($data){
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }
}

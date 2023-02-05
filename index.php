<?php


error_reporting(0);
date_default_timezone_set('PRC');
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
define('BASEDIR',__DIR__);
require 'App/Common/Conf/const.php';
require 'vendor/autoload.php';
require 'SasPHP/SasPHP.php';
$res = SasPHP\SasPHP::start();
echo $res;

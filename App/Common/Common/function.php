<?php
/**
 * User: Hufeng
 * Date: 2020/7/12 17:44
 * Desc: 公用函数库
 */
function httpCurl($url,$param){
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

//生成前多少天日期
function bulidDateArr($nums){
    $dateArr = [];
    $dateArr[] = date("Y-m-d");
    for($i=1;$i<$nums;$i++){
        $date = date('Y-m-d',strtotime("-".$i." day"));
        $dateArr[] = $date;
    }
    return array_reverse($dateArr);
}

/*生成订单号*/
function  buildOrderNo(){
    $timeStr = date('YmdHis');
    $myPid = str_pad(getmypid(), 5, "0", STR_PAD_LEFT);
    $randNum = rand(10,99);
    $orderNo = $timeStr.$myPid.$randNum;
    return $orderNo;
}

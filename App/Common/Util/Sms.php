<?php
/**
 * User: hufeng
 * Date: 2016/12/25
 * Desc：短信类库
 */
namespace App\Common\Util;
require_once('lib/Ucpaas.class.php');

class Sms{

    public function smsSend($tel,$yzm){
        $options['accountsid']='';
        $options['token']='';
        $appid = "";	//应用的ID，可在开发者控制台内的短信产品下查看
        $templateid = "";    //可在后台短信产品→选择接入的应用→短信模板-模板ID，查看该模板ID

        $ucpass = new \Ucpaas($options);
        $param = $yzm; //多个参数使用英文逗号隔开（如：param=“a,b,c”），如为参数则留空
        $mobile = $tel;
        $uid = "";
        return $ucpass->SendSms($appid,$templateid,$param,$mobile,$uid);
    }
}

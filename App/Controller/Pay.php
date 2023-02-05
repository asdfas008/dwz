<?php
/**
 * User: Hufeng
 * Date: 2017/12/04 17:51
 * Desc: TRADE 支付SERVER
 */
namespace App\Controller;

use App\Common\Util\PlateNotify;
use App\Common\Util\Redis;
use App\Model\BroadcastModel;
use App\Model\FamilyModel;
use App\Model\FamilyTaskBonusModel;
use App\Model\FamilyTaskModel;
use App\Model\FamilyTaskUserModel;
use App\Model\FamilyUserModel;
use App\Model\LotteryUserNumModel;
use App\Model\LscUserSickleModel;
use App\Model\OrderExtModel;
use App\Model\OrderModel;
use App\Model\RaceGameModel;
use App\Model\RaceGameUserModel;
use App\Model\UserAssetsModel;
use App\Model\UserModel;

use Payment\Common\PayException;
use Payment\Client\Notify;
use Payment\Client\Charge;
use Payment\Config;

class Pay extends Base {
    /*余额支付*/
    public function moneyPay(){
        return;
    }
    /*充值游戏币*/
    public function balance(){
        $needParam = array(
            'openId'=>array('msg'=>'openId参数异常','type'=>'str'),
            'amount'=>array('msg'=>'amount参数异常','type'=>'float'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uid = $uModel->getUidByOpenId($this->param['openId']);
        if(!$uid){
            return $this->fjson(['code'=>1,'msg'=>'账号异常，请联系客服人员']);
        }
        $orderNo = $this->buildOrderNo();
        $needPayMoney = $this->param['amount'];
        if($needPayMoney <= 0){
            return json_encode(['result'=>false,'msg'=>'待支付金额不能为0']);
        }
        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }
        $oInData = [
            'uid'=>$uid,
            'order_no'=>$orderNo,
            'order_type'=>ORDER_TYPE_CHARGE,
            'pay_money'=>$needPayMoney,
            'pay_type'=>$payType,
            'addtime'=>$this->timeNow
        ];
        $orderModel = new OrderModel();
        $orderModel->insertData($oInData);
        if($payType == ORDER_ALI_PAY){
            $this->aliPaySign('会员充值',$orderNo,$needPayMoney);
        }else{
            $this->wxPaySign('会员充值',$orderNo,$needPayMoney);
        }
        return json_encode($this->outData);
    }
    /*购买抽奖次数*/
    public function lotter(){
        return;
        /*$this->param['openId'] = '5yPlR0j0Rq1Dnx';
        $this->param['paytype'] = 'wxpay';*/
        $needParam = array(
            'openId'=>array('msg'=>'openId参数异常','type'=>'str'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uid = $uModel->getUidByOpenId($this->param['openId']);
        if(!$uid){
            return $this->fjson(['code'=>1,'msg'=>'账号异常，请联系客服人员']);
        }
        $orderNo = $this->buildOrderNo();
        $needPayMoney = 10;
        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }
        $oInData = [
            'uid'=>$uid,
            'order_no'=>$orderNo,
            'order_type'=>ORDER_TYPE_LOTTER,
            'pay_money'=>$needPayMoney,
            'pay_type'=>$payType,
            'addtime'=>$this->timeNow
        ];
        $orderModel = new OrderModel();
        $orderModel->insertData($oInData);
        if($payType == ORDER_ALI_PAY){
            $this->aliPaySign('支付10元抽',$orderNo,$needPayMoney);
        }else{
            $this->wxPaySign('支付10元抽',$orderNo,$needPayMoney);
        }
        return json_encode($this->outData);
    }
    /*支付竞赛报名*/
    public function race(){
        return;
//        $this->param['openId'] = '5yPlR0j0Rq1Dnx';
//        $this->param['paytype'] = 'alipay';
//        $this->param['orderNo'] = '18101215252604892849';
        $needParam = array(
            'openId'=>array('msg'=>'openId参数异常','type'=>'str'),
            'orderNo'=>array('msg'=>'orderNo参数异常','type'=>'str'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uid = $uModel->getUidByOpenId($this->param['openId']);
        if(!$uid){
            return $this->fjson(['code'=>1,'msg'=>'账号异常，请联系客服人员']);
        }
        $orderModel = new OrderModel();
        $orderNo = $this->param['orderNo'];
        $oData = $orderModel->findData(['order_no'=>$orderNo,'status'=>1]);
        if(!$oData){
            return $this->fjson(['code'=>1,'msg'=>'订单已支付']);
        }
        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }

        $needPayMoney = $oData['pay_money'];
        if($payType == ORDER_ALI_PAY){
            $this->aliPaySign('竞赛报名',$orderNo,$needPayMoney);
        }else{
            $this->wxPaySign('竞赛报名',$orderNo,$needPayMoney);
        }
        return json_encode($this->outData);
    }
    /*支付购买镰刀订单*/
    public function sicklePay(){
        $needParam = array(
            'orderNo'=>array('msg'=>'openId参数异常','type'=>'str'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $orderModel = new OrderModel();
        $orderData = $orderModel->findData(['order_no'=>$this->param['orderNo'],'status'=>1]);
        if(!$orderData){
            return $this->fjson(['code'=>1,'msg'=>'订单异常，请重试']);
        }

        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }elseif ($this->param['paytype']=='coinpay'){
            $payType = ORDER_BALANCE_PAY;
        }
        if($payType == ORDER_BALANCE_PAY){
            //扣除用户金币
            $redis = new Redis();
            $lockKey = LOCK_USER_BALANCE.$orderData['uid'];
            if(!$redis->getLock($lockKey)){
                return $this->fjson(['code'=>1,'msg'=>'请求失败，请重试']);
            }
            $uaModel = new UserAssetsModel();
            $uaBool = $uaModel->reduceAssets($orderData['uid'],$orderData['pay_money']*CNY_TO_COIN,BALANCE_SILVER_COIN,BALANCE_ENABLE,'购买镰刀');
            $redis->removeLock($lockKey);
            if($uaBool){
                //调用回调
                $this->dealOrder($orderData['order_no'],ORDER_BALANCE_PAY,'');
                return $this->fjson(['code'=>0,'msg'=>'支付成功']);
            }else{
                return $this->fjson(['code'=>1,'msg'=>'抱歉，您的金币不足']);
            }
        }elseif($payType == ORDER_ALI_PAY){
            $this->aliPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }else{
            $this->wxPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }
        return json_encode($this->outData);
    }
    /*支付创建家族订单*/
    public function buildFamilyPay(){
        $needParam = array(
            'orderNo'=>array('msg'=>'openId参数异常','type'=>'str'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $orderModel = new OrderModel();
        $orderData = $orderModel->findData(['order_no'=>$this->param['orderNo'],'status'=>1]);
        if(!$orderData){
            return $this->fjson(['code'=>1,'msg'=>'订单异常，请重试']);
        }

        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }elseif ($this->param['paytype']=='coinpay'){
            $payType = ORDER_BALANCE_PAY;
        }
        if($payType == ORDER_BALANCE_PAY){
            //扣除用户金币
            $redis = new Redis();
            $lockKey = LOCK_USER_BALANCE.$orderData['uid'];
            if(!$redis->getLock($lockKey)){
                return $this->fjson(['code'=>1,'msg'=>'请求失败，请重试']);
            }
            $uaModel = new UserAssetsModel();
            $uaBool = $uaModel->reduceAssets($orderData['uid'],$orderData['pay_money']*CNY_TO_COIN,BALANCE_SILVER_COIN,BALANCE_ENABLE,'创建家族');
            $redis->removeLock($lockKey);
            if($uaBool){
                //调用回调
                $this->dealOrder($orderData['order_no'],ORDER_BALANCE_PAY,'');
                return $this->fjson(['code'=>0,'msg'=>'支付成功']);
            }else{
                return $this->fjson(['code'=>1,'msg'=>'抱歉，您的金币不足']);
            }

        }elseif($payType == ORDER_ALI_PAY){
            $this->aliPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }else{
            $this->wxPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }
        return json_encode($this->outData);
    }
    /*支付解散家族订单*/
    public function dissolveFamily(){
        $needParam = array(
            'orderNo'=>array('msg'=>'openId参数异常','type'=>'str'),
            'paytype'=>array('msg'=>'paytype参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $orderModel = new OrderModel();
        $orderData = $orderModel->findData(['order_no'=>$this->param['orderNo'],'status'=>1]);
        if(!$orderData){
            return $this->fjson(['code'=>1,'msg'=>'订单异常，请重试']);
        }

        $payType = ORDER_WX_PAY;
        if($this->param['paytype']=='alipay'){
            $payType = ORDER_ALI_PAY;
        }elseif ($this->param['paytype']=='coinpay'){
            $payType = ORDER_BALANCE_PAY;
        }
        if($payType == ORDER_BALANCE_PAY){
            //扣除用户金币
            $redis = new Redis();
            $lockKey = LOCK_USER_BALANCE.$orderData['uid'];
            if(!$redis->getLock($lockKey)){
                return $this->fjson(['code'=>1,'msg'=>'请求失败，请重试']);
            }
            $uaModel = new UserAssetsModel();
            $uaBool = $uaModel->reduceAssets($orderData['uid'],$orderData['pay_money']*CNY_TO_COIN,BALANCE_SILVER_COIN,BALANCE_ENABLE,'解散家族');
            $redis->removeLock($lockKey);
            if($uaBool){
                //调用回调
                $this->dealOrder($orderData['order_no'],ORDER_BALANCE_PAY,'');
                return $this->fjson(['code'=>0,'msg'=>'支付成功']);
            }else{
                return $this->fjson(['code'=>1,'msg'=>'抱歉，您的金币不足']);
            }

        }elseif($payType == ORDER_ALI_PAY){
            $this->aliPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }else{
            $this->wxPaySign('购买商品',$orderData['order_no'],$orderData['pay_money']);
        }
        return json_encode($this->outData);
    }

    /*app支付*/
    //支付宝支付签名
    protected function aliPaySign($subject,$orderNo,$needPayMoney){
        // 订单信息
        $subject = $subject;
        $payData = [
            'body'    => $subject,
            'subject'    => $subject,
            'order_no'    => $orderNo,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => $needPayMoney,// 单位为元 ,最小为0.01
            'goods_type' => '1',// 0—虚拟类商品，1—实物类商品
        ];
        $alipayConf = $this->getConf('pay','alipay');
        try {
            $paramStr = Charge::run(Config::ALI_CHANNEL_APP, $alipayConf, $payData);
            $this->outData['data'] = $paramStr;
            $this->outData['orderNo'] = $orderNo;
        } catch (PayException $e) {
            $this->outData = ['code'=>1,'msg'=>'请求失败，请重试'];
        }
        return;
    }
    //微信支付签名
    protected function wxPaySign($subject,$orderNo,$needPayMoney){
        // 订单信息
        $subject = $subject;
        $payData = [
            'body'    => $subject,
            'subject'    => $subject,
            'order_no'    => $orderNo,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => $needPayMoney,// 微信沙箱模式，需要金额固定为3.01
            'client_ip' => '127.0.0.1',// 客户地址
        ];
        $wxPayConf = $this->getConf('pay','wxpay');
        try {
            $sign = Charge::run(Config::WX_CHANNEL_APP, $wxPayConf, $payData);
            $sign['orderNo'] = $orderNo;
            $this->outData['data'] = $sign;
        } catch (PayException $e) {
            $this->outData = ['code'=>1,'msg'=>'请求失败，请重试'];
        }
        return;
    }

    /*支付回调*/
    //支付宝异步回调
    public function aliPayAsync(){
        $callback = new PlateNotify();
        $aliPayConf = $this->getConf('pay','alipay');
        $type = 'ali_charge';
        Notify::run($type, $aliPayConf, $callback);// 处理回调，内部进行了签名检查
    }
    public function aliPaySync(){
        return;
    }

    //微信异步回调
    public function wxPayAsync(){
        $callback = new PlateNotify();
        $wxPayConf = $this->getConf('pay','wxpay');
        $type = 'wx_charge';
        Notify::run($type, $wxPayConf, $callback);
    }
    public function wxPaySync(){
        return;
    }

    //处理订单
    public function dealOrder($orderNo,$payType,$payedExt){
        $payTypeArr = $this->getConf('business','paytype');
        $payNameStr = $payTypeArr[$payType];
        $orderModel = new OrderModel();
        $orderRes = $orderModel->findData(['order_no'=>$orderNo,'status'=>1],['id','order_type','uid','pay_money']);
        if(!$orderRes){return;}
        $orderModel->editData(['id'=>$orderRes['id']],['status'=>2,'paytime'=>date('Y-m-d H:i:s'),'pay_type'=>$payType,'payed_ext'=>$payedExt]);
        //充值游戏币
        $uaModel = new UserAssetsModel();
        if($orderRes['order_type']==ORDER_TYPE_CHARGE){
            $uaModel->addAssets($orderRes['uid'],$orderRes['pay_money']*CNY_TO_COIN,BALANCE_SILVER_COIN,BALANCE_ENABLE,$payNameStr);
            //代理提成
//            $uaModel->calcLeaderAssets($orderRes['uid'],$orderRes['pay_money'],1);
        }
        //支付购买镰刀订单
        elseif($orderRes['order_type']==ORDER_TYPE_BUYSOCKLE){
            $snum = intval($orderRes['pay_money']/SICKLE_PRICE);
            $lusData = [
                'uid'=>$orderRes['uid'],
                'snum'=>$snum,
                'buytime'=>$this->timeNow,
                'endtime'=>date('Y-m-d H:i:s',time()+(86400*30))
            ];
            $lusModel = new LscUserSickleModel();
            $lusModel->insertData($lusData);
            //添加通告信息
            if($snum>=20){
                $uModel = new UserModel();
                $uData = $uModel->findData(['id'=>$orderRes['uid']]);
                $bModel = new BroadcastModel();
                $bModel->insertData(['info'=>'土豪！'.$uData['nick_name'].' 一次购买了'.$snum.'把镰刀']);
            }
        }
        //支付创建家族订单
        elseif($orderRes['order_type']==ORDER_TYPE_BUILDFAMILY){
            $oeModel = new OrderExtModel();
            $oeData = $oeModel->findData(['oid'=>$orderRes['id']]);
            $fModel = new FamilyModel();
            $fid = $fModel->insertData(['fname'=>$oeData['ext'],'uid'=>$orderRes['uid']]);
            $fuModel = new FamilyUserModel();
            $fuModel->insertData(['fid'=>$fid,'uid'=>$orderRes['uid'],'degree'=>3,'status'=>2]);
        }
        //支付解散家族订单
        elseif($orderRes['order_type']==ORDER_TYPE_DISSOLVEFAMILY){
            //删除家族下所有数据
            $fModel = new FamilyModel();
            $fData = $fModel->findData(['uid'=>$orderRes['uid']]);
            $fModel->delData(['fid'=>$fData['fid']]);
            $ftModel = new FamilyTaskModel();
            $ftModel->delData(['fid'=>$fData['fid']]);
            $ftbModel = new FamilyTaskBonusModel();
            $ftbModel->delData(['fid'=>$fData['fid']]);
            $ftuModel = new FamilyTaskUserModel();
            $ftuModel->delData(['fid'=>$fData['fid']]);
            $fuModel = new FamilyUserModel();
            $fuModel->delData(['fid'=>$fData['fid']]);
        }
        //增加支付抽次数
        elseif($orderRes['order_type']==ORDER_TYPE_LOTTER){
            return;
            $lunModel = new LotteryUserNumModel();
            $lunModel->addLeftNum($orderRes['uid'],'pay_num',10);
            //代理提成
            $uaModel->calcLeaderAssets($orderRes['uid'],10,1);
        }
        //报名竞赛
        elseif($orderRes['order_type']==ORDER_TYPE_RACE){
            return;
            $oeModel = new OrderExtModel();
            $oeData = $oeModel->findData(['oid'=>$orderRes['id']]);
            $rgid = $oeData['express_no'];
            $rguModel = new RaceGameUserModel();
            $rguModel->insertData(['rgid'=>$rgid,'uid'=>$orderRes['uid'],'need_money'=>$orderRes['pay_money']]);
            $rgModel = new RaceGameModel();
            $rgData = $rgModel->findData(['id'=>$rgid]);
            $applyNum = $rgData['apply_num']+1;
            $needNum = [1=>100,2=>1000,3=>10000];
            $where = [];
            $where['apply_num'] = $applyNum;
            if($applyNum>=$needNum[$rgData['type']]){
                $where['status'] = 2;
                $where['end_time'] = date('Y-m-d H:i:s');
                $where['open_time'] = date('Y-m-d H:i:s',strtotime("+3 days"));
            }
            $rgModel->editData(['id'=>$rgid],$where);
            if($applyNum>=$needNum[$rgData['type']]){
                //处理用户初始资产
                $rguModel->calcUserStartMoney($rgid);
            }
            //代理提成
            $uaModel->calcLeaderAssets($orderRes['uid'],$orderRes['pay_money'],2);
        }
    }
}

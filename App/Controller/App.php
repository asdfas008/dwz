<?php
/**
 * User: Hufeng
 * Date: 2017/12/04 17:51
 * Desc: Models-App api
 */
namespace App\Controller;
use App\Common\Util\Qiniu;
use App\Common\Util\Redis;
use App\Common\Util\Sms;
use App\Common\Util\HashId;

use App\Model\AppVersionModel;
use App\Model\CompanyMasterModel;
use App\Model\CompanyModel;
use App\Model\MatchModel;
use App\Model\MatchModelModel;
use App\Model\ModelBalanceModel;
use App\Model\ModelHonorModel;
use App\Model\ModelImgModel;
use App\Model\ModelModel;
use App\Model\ModelsAgreeModel;
use App\Model\ModelVipModel;
use App\Model\OrderModel;
use App\Model\SysConfigModel;
use App\Model\UserModel;
use App\Model\UserNoticeModel;
use App\Model\UserSuggestModel;

class App extends Base {
    public function __construct(){
        parent::__construct();
    }

    protected function regSmsCode($tel,$code){
        $this->redis = new Redis();
        $rkey = 'SMSCODE-'.$tel;
        $redisGets = $this->redis->client->hmget($rkey,['smsCode','sendTime']);
        $timeNow = time();
        $overTime = $timeNow - $redisGets[1];
        if($overTime<4300){
            if($code == $redisGets[0]){
                $this->redis->client->del([$rkey]);
            }else{
                $this->outData = ['code'=>401,'msg'=>'验证码错误，请重新输入'];
            }
        }else{
            $this->outData = ['code'=>401,'msg'=>'验证码已失效，请重新获取验证码'];
        }
        return;
    }
    protected function getUidByToken($token){
        $this->redis = new Redis();
        $rkey = 'USERTOKEN-'.$token;
        $uid = $this->redis->client->get($rkey);
        if($uid){
            return $uid;
        }else{
            return 0;
        }
    }

    /*七牛上传token*/
    public function qnUpToken(){
        $qiniu = new Qiniu();
        $this->outData['data'] = $qiniu->getToken();
        return $this->fjson($this->outData);
    }
    /*发送手机验证码*/
    public function smsCode(){
//        $this->param['mobile'] = '17620135606';
        $needParam = array(
            'mobile'=>array('msg'=>'mobile参数异常','type'=>'tel')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();

        $rkey = 'SMSCODE-'.$this->param['mobile'];
        $redisGets = $this->redis->client->hmget($rkey,['smsCode','sendTime']);
        $timeNow = time();
        $overTime = $timeNow - $redisGets[1];
        //60秒内，不允许重复发送短息
        if($overTime<60){
            $leftTime = 60 - $overTime;
            return $this->fjson(['code'=>1,'msg'=>'发送过于频繁，'.$leftTime.'秒后再试']);
        }
        //5分钟内同类型的验证码保持相同
        if($overTime<300){
            $code = $redisGets[0];
        }else{
            $code = rand(100000,999999);
        }

        $rdata = ['smsCode'=>$code,'sendTime'=>time()];
        $this->redis->client->hmset($rkey,$rdata);
        $sms = new Sms();
        $TemplateParam = ["code"=>$code];
        $resq = $sms->smsSend($this->param['mobile'],json_encode($TemplateParam),'checkCodeTplId');
        if($resq){
            return $this->fjson($this->outData);
        }else{
            return $this->fjson(['code'=>401,'msg'=>'发送失败']);
        }
    }
    /*手机短信登录*/
    public function login(){
//        $this->param['mobile'] = '13056116151';
//        $this->param['smsCode'] = '119076';
        $needParam = array(
            'mobile'=>array('msg'=>'mobile参数异常','type'=>'tel'),
            'smsCode'=>array('msg'=>'smsCode参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        $this->regSmsCode($this->param['mobile'],$this->param['smsCode']);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }

        //检测手机号是否存在
        $uModel = new UserModel();
        $userInfo = $uModel->findData(['mobile'=>$this->param['mobile']],['uid','nick_name','mobile','avatar']);
        if(!$userInfo){
            //注册
            $userInData = [
                'nick_name'=>$this->param['mobile'],
                'mobile'=>$this->param['mobile'],
                'create_time'=>$this->timeNow,
            ];
            $uid = $uModel->insertData($userInData);
            //创建openId
            $hashId = new HashId();
            $token = $hashId->buildAppsecret($uid);
            $userData = [
                'nick_name'=>"models",
                'mobile'=>$this->param['mobile'],
                'token'=>$token,
                'avatar'=>''
            ];
        }else{
            $hashId = new HashId();
            $token = $hashId->buildAppsecret($userInfo['uid']);
            $userData = [
                'nick_name'=>$userInfo['nick_name'],
                'mobile'=>$userInfo['mobile'],
                'token'=>$token,
                'avatar'=>$userInfo['avatar']
            ];
            $uid = $userInfo['uid'];
        }
        $this->redis = new Redis();
        $rkey = 'USER-'.$uid;
        $oldToken = $this->redis->client->get($rkey);
        $this->redis->client->del($oldToken);
        $this->redis->client->set($rkey,'USERTOKEN-'.$token);
        $rkey = 'USERTOKEN-'.$token;
        $this->redis->client->set($rkey,$uid);
        return $this->fjson(['code'=>0,'msg'=>'登录成功','data'=>$userData]);
    }
    /*模特信息认证*/
    public function modelAuth(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['real_name'] = '胡峰';
//        $this->param['avatar'] = 'http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg';
//        $this->param['height'] = '180';
//        $this->param['weight'] = '90';
//        $this->param['bwh'] = '82 59 84';
//        $this->param['shoes'] = '40';
//        $this->param['sign_company'] = '暂无';
//        $this->param['area'] = '北京';
//        $this->param['tel'] = '17620135606';
//        $this->param['main_img'] = 'https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=1109230556,1212598636&fm=26&gp=0.jpg';
//        $this->param['mtype'] = '1';

        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'real_name'=>array('msg'=>'real_name参数异常','type'=>'str'),
            'height'=>array('msg'=>'height参数异常','type'=>'str'),
            'weight'=>array('msg'=>'weight参数异常','type'=>'str'),
            'bwh'=>array('msg'=>'bwh参数异常','type'=>'str'),
            'shoes'=>array('msg'=>'shoes参数异常','type'=>'str'),
            'area'=>array('msg'=>'area参数异常','type'=>'str'),
            'tel'=>array('msg'=>'tel参数异常','type'=>'tel'),
            'main_img'=>array('msg'=>'main_img参数异常','type'=>'str'),
            'mtype'=>array('msg'=>'mtype参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        //更新用户头像，更新用户昵称
        $uModel = new UserModel();
        $uModel->editData(['uid'=>$uid],[
            'nick_name'=>$this->param['real_name'],
            'avatar'=>$this->param['avatar']
        ]);
        //保存模特信息
        unset($this->param['nick_name']);
        unset($this->param['avatar']);
        unset($this->param['token']);
        $this->param['uid'] = $uid;
        $mModel = new ModelModel();
        $mData = $mModel->findData(['uid'=>$uid]);
        if($mData){
            $mModel->editData(['uid'=>$uid],$this->param);
        }else{
            $mModel->insertData($this->param);
        }
        return $this->fjson($this->outData);
    }
    /*添加模特模卡、展示图*/
    public function modelPic(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['card_pic'] = 'http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg';
//        $this->param['show_pic'] = '["http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg","http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg"]';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'card_pic'=>array('msg'=>'cardPic参数异常','type'=>'str'),
            'show_pic'=>array('msg'=>'showPic参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        //更新用户模卡
        $mModel = new ModelModel();
        $mModel->editData(['uid'=>$uid],[
            'card_pic'=>$this->param['card_pic']
        ]);
        //保存模特展示图
        $showPicArr = json_decode($_POST['show_pic']);
        $miModel = new ModelImgModel();
        $miModel->delData(['uid'=>$uid,'type'=>1]);
        foreach($showPicArr as $pic){
            $miData = [
                'uid'=>$uid,
                'pic'=>$pic
            ];
            $miModel->insertData($miData);
        }
        return $this->fjson($this->outData);
    }
    /*查询模特认证信息*/
    public function modelAuthInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['uid'=>$uid]);
        $mModel = new ModelModel();
        $mData = $mModel->findData(['uid'=>$uid]);
        $mData['nick_name'] = $uData['nick_name'];
        $mData['avatar'] = $uData['avatar'];
        $this->outData['data'] = $mData;
        return $this->fjson($this->outData);
    }
    /*建议*/
    public function suggest(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['suggest'] = '很好再接再厉';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'suggest'=>array('msg'=>'请输入建议','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $usModel = new UserSuggestModel();
        $usData = [
            'uid'=>$uid,
            'suggest'=>$this->param['suggest'],
            'create_time'=>$this->timeNow,
        ];
        $usModel->insertData($usData);
        return $this->fjson($this->outData);
    }
    /*添加荣誉奖项*/
    public function honor(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['honor'] = '2018年北京大赛冠军';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'honor'=>array('msg'=>'请输入荣誉','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $mhModel = new ModelHonorModel();
        $usData = [
            'uid'=>$uid,
            'honor'=>$this->param['honor'],
        ];
        $mhModel->insertData($usData);
        return $this->fjson($this->outData);
    }
    /*荣誉奖项列表*/
    public function honorList(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $mhModel = new ModelHonorModel();
        $dataList = $mhModel->queryData(['uid'=>$uid]);
        $this->outData['data'] = $dataList;
        return $this->fjson($this->outData);
    }
    /*企业认证*/
    public function companyAuth(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['user_name'] = 'hufeng';
//        $this->param['tel'] = '17620135606';
//        $this->param['company_name'] = '百度公司';
//        $this->param['job_name'] = '主管';
//        $this->param['license_pic'] = 'http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'user_name'=>array('msg'=>'user_name参数异常','type'=>'str'),
            'tel'=>array('msg'=>'tel参数异常','type'=>'tel'),
            'company_name'=>array('msg'=>'company_name参数异常','type'=>'str'),
            'job_name'=>array('msg'=>'job_name参数异常','type'=>'str'),
            'license_pic'=>array('msg'=>'license_pic参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        //保存公司信息
        unset($this->param['token']);
        $this->param['uid'] = $uid;
        $cModel = new CompanyModel();
        $cData = $cModel->findData(['uid'=>$uid]);
        if($cData){
            $cModel->editData(['uid'=>$uid],$this->param);
        }else{
            $cModel->insertData($this->param);
        }
        return $this->fjson($this->outData);
    }
    /*获取企业认证信息*/
    public function companyAuthInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $cModel = new CompanyModel();
        $cData = $cModel->findData(['uid'=>$uid]);
        $this->outData['data'] = $cData;
        return $this->fjson($this->outData);
    }
    /*获取企业营业执照*/
    public function companylicensePic(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $cModel = new CompanyModel();
        $cData = $cModel->findData(['uid'=>$uid]);
        $data['license_pic'] = '';
        if($cData){
            $data['license_pic'] = $cData['license_pic'];
        }else{
            $cmModel = new CompanyMasterModel();
            $cmData = $cmModel->findData(['company_uid'=>$uid]);
            if($cmData){
                $data['license_pic'] = $cmData['license_pic'];
            }
        }
        $this->outData['data'] = $data;
        return $this->fjson($this->outData);
    }
    /*联系主办方*/
    public function companyMaster(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['license_pic'] = 'http://i2.hdslb.com/bfs/face/d79637d472c90f45b2476871a3e63898240a47e3.jpg';
//        $this->param['message'] = '留言内容';
//        $this->param['mid'] = 1;
        $needParam = array(
            'token'=>array('msg'=>'请登录后提交','type'=>'str'),
            'mid'=>array('msg'=>'mid','type'=>'int'),
            'message'=>array('msg'=>'message参数异常','type'=>'str'),
            'license_pic'=>array('msg'=>'license_pic参数异常','type'=>'str'),
            'user_name'=>array('msg'=>'user_name参数异常','type'=>'str'),
            'tel'=>array('msg'=>'tel参数异常','type'=>'tel'),
            'company_name'=>array('msg'=>'company_name参数异常','type'=>'str'),
            'job_name'=>array('msg'=>'job_name参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        //保存公司信息
        $cModel = new CompanyModel();
        $cData = $cModel->findData(['uid'=>$uid]);
        $newCdata = [
            'uid'=>$uid,
            'user_name'=>$this->param['user_name'],
            'tel'=>$this->param['tel'],
            'company_name'=>$this->param['company_name'],
            'job_name'=>$this->param['job_name'],
            'license_pic'=>$this->param['license_pic'],
        ];
        if($cData){
          $cModel->editData(['uid'=>$uid],$newCdata);
        }else{
          $newCdata['check_status'] = 1;
          $cModel->insertData($this->param);
        }

        $cmModel = new CompanyMasterModel();
        $cmData = [
            'company_uid'=>$uid,
            'message'=>$this->param['message'],
            'mid'=>$this->param['mid'],
            'create_time'=>$this->timeNow,
        ];
        $cmModel->insertData($cmData);
        return $this->fjson($this->outData);
    }

    /*赛事列表-赛事名称，赛事列表图，赛事主图，开始时间，结束时间，总条数*/
    public function matchList(){
//        $this->param['pageIndex'] = 1;
//        $this->param['pageSize'] = 10;
        $needParam = array(
            'pageIndex'=>array('msg'=>'pageIndex参数异常','type'=>'int'),
            'pageSize'=>array('msg'=>'pageSize参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $mWhere = [
            'status'=>1,
            'LIMIT' => [($this->param['pageIndex']-1)*$this->param['pageSize'], $this->param['pageSize']],
            'ORDER'=>['edate'=>'DESC']
        ];
        $mModel = new MatchModel();
        $mList = $mModel->queryData($mWhere,['mid','title','main_pic','list_pic','sdate','edate']);
        $totalNum = $mModel->getTotalNum($mWhere);
        $this->outData['data']['totals'] = $totalNum;
        $this->outData['data']['matchList'] = $mList;
        return $this->fjson($this->outData);
    }
    /*赛事详情*/
    public function matchInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['mid'] = 1;
        $needParam = array(
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = 0;
        if($this->param['token']){
            $uid = $this->getUidByToken($this->param['token']);
        }
        //赛事详情
        $mModel = new MatchModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);

        if(strtotime($mData['edate'])+86400>time()){
            $mData['matchStatus'] = 1;//可以报名
        }else{
            $mData['matchStatus'] = 2;//报名已结束
        }

        //模特是否已报名
        if($uid){
            $mmModel = new MatchModelModel();
            $mmData = $mmModel->findData(['uid'=>$uid,'mid'=>$this->param['mid']]);
            if($mmData){
                $mData['matchStatus'] = 3;//已报名
            }
        }
        $this->outData['data'] = $mData;
        return $this->fjson($this->outData);
    }
    /*模特报名赛事（赛事是否结束，模特是否验证，是否已报名）*/
    public function matchModel(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['mid'] = 1;
        return $this->fjson(['code'=>405,'msg'=>'接口不存在']);
        /*$needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        //赛事详情
        $mModel = new MatchModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);

        if(strtotime($mData['edate'])+86400<=time()){
            return $this->fjson(['code'=>401,'msg'=>'报名已结束']);
        }
        $mmModel = new MatchModelModel();
        $mmData = $mmModel->findData(['uid'=>$uid,'mid'=>$this->param['mid']]);
        if($mmData){
            return $this->fjson(['code'=>401,'msg'=>'您已报过名了！']);
        }
        //模特是否认证
        $modelModel = new ModelModel();
        $modelData = $modelModel->findData(['uid'=>$uid]);
        if(!$modelData){
            return $this->fjson(['code'=>401,'msg'=>'请先提交模特认证信息！']);
        }elseif($modelData && $modelData['check_status']!=2){
            return $this->fjson(['code'=>401,'msg'=>'您的认证信息还在审核中！']);
        }
        $mmModel->insertData(['mid'=>$this->param['mid'],'uid'=>$uid]);
        $mModel->editData(['mid'=>$this->param['mid']],['join_nums[+]'=>1]);
        $this->outData['msg'] = '报名成功';
        return $this->fjson($this->outData);*/
    }
    /*模特列表*/
    public function modelList(){
//        $this->param['pageIndex'] = 1;
//        $this->param['pageSize'] = 10;
//        $this->param['mtype'] = 0;
        $needParam = array(
            'pageIndex'=>array('msg'=>'pageIndex参数异常','type'=>'int'),
            'pageSize'=>array('msg'=>'pageSize参数异常','type'=>'int'),
            'mtype'=>array('msg'=>'mtype参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $querySql = "SELECT a.mid,a.uid,a.real_name,c.`nick_name`,c.`avatar`,a.`hot`,a.`height`,a.`weight`,a.`bwh`,a.`shoes`,b.`last_date` FROM `models_model` a
                    LEFT JOIN `models_model_vip` b ON a.uid = b.uid
                    LEFT JOIN `models_user` c ON a.uid = c.uid
                    WHERE a.check_status = 2";
        if($this->param['mtype']){
            $querySql .= " AND a.mtype = ".$this->param['mtype'];
        }
        $start = ($this->param['pageIndex']-1)*$this->param['pageSize'];
        $limt = $this->param['pageSize'];

        $querySql .= " ORDER BY a.`hot` DESC,b.`last_date` DESC limit ".$start.",".$limt;

        $mModel = new ModelModel();
        $dataList = $mModel->dbConn->query($querySql)->fetchAll();
        $modelList = [];
        foreach($dataList as $val){
            $tmpData = [];
            $tmpData['mid'] = $val['mid'];
            $tmpData['uid'] = $val['uid'];
            $tmpData['real_name'] = $val['real_name'];
            $tmpData['nick_name'] = $val['nick_name'];
            $tmpData['avatar'] = $val['avatar'];
            $tmpData['hot'] = $val['hot'];
            $tmpData['height'] = $val['height'];
            $tmpData['weight'] = $val['weight'];
            $tmpData['bwh'] = $val['bwh'];
            $tmpData['shoes'] = $val['shoes'];
            $tmpData['vip'] = 0;
            if($val['last_date'] && (strtotime($val['last_date'])+86400)>time()){
                $tmpData['vip'] = 1;
            }
            $modelList[] = $tmpData;
        }

        $mWhere = ["check_status"=>2];
        if($this->param['mtype']){
            $mWhere['mtype'] = $this->param['mtype'];
        }
        $totalNum = $mModel->getTotalNum($mWhere);
        $this->outData['data']['totals'] = $totalNum;
        $this->outData['data']['modelList'] = $modelList;
        return $this->fjson($this->outData);
    }
    /*模特详情*/
    public function modelInfo(){
//        $this->param['mid'] = 1;
        $needParam = array(
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $mModel = new ModelModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);
        if(!$mData){
            return $this->fjson(['code'=>405,'msg'=>'模特信息不存在']);
        }
        $mModel->editData(['mid'=>$this->param['mid']],['hot[+]'=>1]);
        $uModel = new UserModel();
        $uData = $uModel->findData(['uid'=>$mData['uid']]);
        $mData['nick_name'] = $uData['nick_name'];
        $mData['avatar'] = $uData['avatar'];
        $mvModel = new ModelVipModel();
        $mvData = $mvModel->findData(['uid'=>$mData['uid']]);
        $mData['vip'] = 0;
        if($mvData && (strtotime($mvData['last_date'])+86400)>time()){
            $mData['vip'] = 1;
        }
        $this->outData['data']['mData'] = $mData;
        $mhModel = new ModelHonorModel();
        $mhData = $mhModel->queryData(['uid'=>$mData['uid']],'honor');
        $this->outData['data']['mhData'] = $mhData;
        $miModel = new ModelImgModel();
        $miData = $miModel->queryData(['uid'=>$mData['uid']],'pic');
        $this->outData['data']['miData'] = $miData;
        return $this->fjson($this->outData);
    }
    /*用户认证状态、会员状态，热度*/
    public function userInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $mModel = new ModelModel();
        $mData = $mModel->findData(['uid'=>$uid,'check_status'=>2]);
        $resData = ['check'=>0,'vip'=>0,'hot'=>0];
        if($mData){
            $resData['check'] = 1;
            $resData['hot'] = $mData['hot'];
        }
        $mvModel = new ModelVipModel();
        $mvData = $mvModel->findData(['uid'=>$mData['uid']]);
        if($mvData && (strtotime($mvData['last_date'])+86400)>time()){
            $resData['vip'] = 1;
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['uid'=>$uid]);
        $resData['nick_name'] = $uData['nick_name'];
        $resData['avatar'] = $uData['avatar'];
        $this->outData['data'] = $resData;
        return $this->fjson($this->outData);
    }
    /*企业联系模特需支付价格*/
    public function companyGetModelInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['mid'] = 1;
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        //查询模特信息
        $mModel = new ModelModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);
        if(!$mData){
            return $this->fjson(['code'=>405,'msg'=>'模特信息不存在']);
        }
        //查询自定义价格
        $mbModel = new ModelBalanceModel();
        $mbData = $mbModel->findData(['uid'=>$mData['uid']]);
        if($mbData){
            $price = $mbData['balance'];
        }else{
            $scModel = new SysConfigModel();
            $scData = $scModel->findData(['param_key'=>'MODEL_INFO_BASE_PRICE']);
            $price = $scData['param_value'];
        }
        $this->outData['data'] = $price;
        return $this->fjson($this->outData);
    }
    /* 查询模特会员信息 是否会员，是否首月， 会员还有多少天到期 会员价格  */
    public function modelVipInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $mvModel = new ModelVipModel();
        $mvData = $mvModel->findData(['uid'=>$uid]);
        $resData['vip'] = 0;
        $resData['first_month'] = 0;
        $resData['vip_have_days'] = 0;
        if(!$mvData){
            $resData['first_month'] = 1;
        }
        if($mvData && (strtotime($mvData['last_date'])+86400)>time()){
            $resData['vip'] = 1;
            $resData['vip_have_days'] = ((strtotime($mvData['last_date'])+86400)-strtotime(date("Y-m-d")))/86400;
        }
        $scModel = new SysConfigModel();
        if($resData['first_month']){
            $scData = $scModel->findData(['param_key'=>'MODEL_VIP_FIRST_PRICE']);
            $resData['vip_price'] = $scData['param_value'];
        }else{
            $scData = $scModel->findData(['param_key'=>'MODEL_VIP_NOFIRST_PRICE']);
            $resData['vip_price'] = $scData['param_value'];
        }
        $this->outData['data'] = $resData;
        return $this->fjson($this->outData);
    }

    /*消息列表(分页查询，第一页查询会员是否到期，是否有新的大赛（认证的模特），自动添加到表中)*/
    public function noticeList(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['pageIndex'] = 1;
//        $this->param['pageSize'] = 10;
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'pageIndex'=>array('msg'=>'pageIndex参数异常','type'=>'int'),
            'pageSize'=>array('msg'=>'pageSize参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        $nModel = new UserNoticeModel();
        if($this->param['pageIndex'] == 1){
            //检测会员到期
            $mvModel = new ModelVipModel();
            $mvData = $mvModel->findData(['uid'=>$uid]);
            if($mvData && (strtotime($mvData['last_date'])+86400)<=time()){
                $lastDate = date('Y-m-d',strtotime($mvData['last_date'])+86400);
                $nvData = $nModel->findData(['uid'=>$uid,'notice_type'=>1,'vip_last_date'=>$lastDate]);
                if(!$nvData){
                    $nvData = [
                        'uid'=>$uid,
                        'notice_title'=>"您的会员已到期",
                        'notice_content'=>"尊敬的Models会员用户，您的会员已到期。",
                        'notice_type'=>1,
                        'vip_last_date'=>$lastDate,
                        'create_time'=>$lastDate,
                    ];
                    $nModel->insertData($nvData);
                }
            }
            //检测是否有新的大赛
            $matchModel = new MatchModel();
            $matchData = $matchModel->findData(['edate[>=]'=>date('Y-m-d'),'ORDER'=>['mid'=>'DESC']]);
            if($matchData){
                $nmData = $nModel->findData(['uid'=>$uid,'notice_type'=>3,'mid'=>$matchData['mid']]);
                if(!$nmData){
                    $nmData = [
                        'uid'=>$uid,
                        'notice_title'=>$matchData['title'].'开启',
                        'notice_content'=>$matchData['title'].'报名已经开启。',
                        'notice_type'=>3,
                        'mid'=>$matchData['mid'],
                        'create_time'=>$this->timeNow,
                    ];
                    $nModel->insertData($nmData);
                }
            }
        }
        $nWhere = [
            'uid' => $uid,
            'LIMIT' => [($this->param['pageIndex']-1)*$this->param['pageSize'], $this->param['pageSize']],
            'ORDER'=>['is_read'=>'ASC','nid'=>'DESC']
        ];

        $nList = $nModel->queryData($nWhere,['nid','notice_title','create_time','is_read']);
        unset($nWhere['LIMIT']);
        unset($nWhere['ORDER']);
        $totalNum = $nModel->getTotalNum($nWhere);
        $this->outData['data']['totals'] = $totalNum;
        $this->outData['data']['noticeList'] = $nList;
        return $this->fjson($this->outData);
    }
    /*消息详情 -标记为已读*/
    public function noticeInfo(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
//        $this->param['nid'] = 2;
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'nid'=>array('msg'=>'nid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        $unModel = new UserNoticeModel();
        $unData = $unModel->findData(['nid'=>$this->param['nid'],'uid'=>$uid]);
        if(!$unData){
            return $this->fjson(['code'=>405,'msg'=>'消息不存在']);
        }
        $this->outData['data']['notice'] = $unData;
        $this->outData['data']['company'] = new \stdClass();
        if($unData['notice_type']==2){
            $cModel = new CompanyModel();
            $cData = $cModel->findData(['uid'=>$unData['company_uid']]);
            $this->outData['data']['company'] = $cData;
        }
        $vip = 0;
        if($unData['notice_type']==1){
          $mvModel = new ModelVipModel();
          $mvData = $mvModel->findData(['uid'=>$uid]);
          if($mvData && (strtotime($mvData['last_date'])+86400)<time()){
            $vip = 1;
          }
        }
        $this->outData['data']['vipEnd'] = $vip;
        $unModel->editData(['nid'=>$this->param['nid']],['is_read'=>1]);
        return $this->fjson($this->outData);
    }
    /*app版本检测*/
    public function version(){
//        $this->param['type'] = 1;
        $type = 1;
        if($this->param['type']){
            $type = $this->param['type'];
        }
        $avModel = new AppVersionModel();
        $data = $avModel->findData(['type'=>$type]);
        $this->outData['data'] = $data ? $data : [];
        return $this->fjson($this->outData);
    }
    /*模特报名赛事列表*/
    public function modelMatchList(){
//        $this->param['token'] = '7630046owa9bly9bmpbaqe513105';
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
          );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }

        $querySql = "SELECT b.`title` FROM `models_match_model` a
                        LEFT JOIN `models_match` b ON a.`mid` = b.`mid`
                        WHERE a.`uid` = ".$uid;
        $mmModel = new MatchModelModel();
        $mmList = $mmModel->dbConn->query($querySql)->fetchAll();
        $data = [];
        foreach($mmList as $val){
            $data[] = $val['title'];
        }
        $this->outData['data'] = $data;
        return $this->fjson($this->outData);
    }

    /*分享模特*/
    public function modelShare(){
//        $this->param['mid'] = 1;
        $needParam = array(
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $mModel = new ModelModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);
        $resData = [];
        $resData['title'] = $mData['real_name'].': 身高'.$mData['height'].'CM'.'，体重'.$mData['weight'].'KG，'.' 三围'.$mData['bwh'];
        $resData['img'] = $mData['card_pic'];
        $this->outData['data'] = $resData;
        return $this->fjson($this->outData);
    }
    /*分享赛事*/
    public function matchShare(){
//        $this->param['mid'] = 1;
        $needParam = array(
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        //赛事详情
        $mModel = new MatchModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);
        $resData = [];
        $resData['title'] = $mData['title'];
        $resData['img'] = $mData['main_pic'];
        $this->outData['data'] = $resData;
        return $this->fjson($this->outData);
    }

    //会员充值订单
    public function modelVipOrder(){
//      $this->param['token'] = '7002966owa9bly9bmpbaqe399711';
//      $this->param['payType'] = 2;
      $needParam = array(
        'token'=>array('msg'=>'token参数异常','type'=>'str'),
        'payType'=>array('msg'=>'payType参数异常','type'=>'int'),
      );
      $this->regArguments($needParam,$this->param);
      if($this->outData['code']){
        return $this->fjson($this->outData);
      }
      $uid = $this->getUidByToken($this->param['token']);
      if(!$uid){
        return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
      }
      if($this->param['payType']!=2){
        return $this->fjson(['code'=>405,'msg'=>'目前仅支持支付宝支付']);
      }

      $mvModel = new ModelVipModel();
      $mvData = $mvModel->findData(['uid'=>$uid]);
      $first_month = 0;
      if(!$mvData){
        $first_month = 1;
      }
      $scModel = new SysConfigModel();
      if($first_month){
        $scData = $scModel->findData(['param_key'=>'MODEL_VIP_FIRST_PRICE']);
        $vip_price = $scData['param_value'];
      }else{
        $scData = $scModel->findData(['param_key'=>'MODEL_VIP_NOFIRST_PRICE']);
        $vip_price = $scData['param_value'];
      }
      //生成订单
      $orderNo = buildOrderNo();
      $orderData = [
        'order_no'=>$orderNo,
        'uid'=>$uid,
        'order_money'=>$vip_price,
        'create_time'=>date("YY-mm-dd H:i:s"),
        'order_type'=>1
      ];
      $oModel = new OrderModel();
      $oModel->insertData($orderData);

      /*生成支付参数*/
      //预下单
      $orderDetail = [];
      $orderDetail[] = [
        "productDesc"=>"modelsVip",
        "orderSeqNo"=>"001",
        "productId"=>"100",
        "rcvMerchantId"=>LKL_MerchantId,
        "detailOrderId"=>$orderNo,
        "rcvMerchantIdName"=>"Models",
        "showUrl"=>"http://127.0.0.1/models",
        "orderAmt"=>($vip_price*100)."",
        "shareFee"=>"0",
        "productName"=>"modelsVip"
      ];
      $orderDetailJson = json_encode($orderDetail,JSON_UNESCAPED_SLASHES);
      $postData = [
        "charset"=>"00",
        "validNum"=>"15",
        "orderId"=>$orderNo,
        "version"=>"1.0",
        "splitType"=>"1",
        "totalAmount"=>($vip_price*100)."",
        "orderTime"=>date("YmdHis"),
        "validUnit"=>"00",
        "merchantId"=>LKL_MerchantId,
        "requestId"=>"modelsVip".time().rand(1000,9999),
        "service"=>"EntOffLinePayment",
        "clientIP"=>"127.0.0.1",
        "signType"=>"RSA",
        "currency"=>"CNY",
        "orderDetail"=>$orderDetailJson,
        "offlineNotifyUrl"=>LKL_ASYNC_URL,
        "tradeType"=>"ALIPAYAPP"
      ];
      $postDataStr = formatParaMap($postData);
      $certs = $this->getPkey();
      if(!$certs){
        return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
      }
      $merchantSign = $this->sha256Str($postDataStr,$certs);
      if(!$merchantSign){
        return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
      }
      $postData['merchantSign'] = $merchantSign;
      $res = httpCurlQuery(LKL_API_URL,$postData);
      $this->outData['data'] = lalDataToArr($res);
      return $this->fjson($this->outData);

      /*$resArr = explode('&',$res);
      $token = str_replace("token=",'',$resArr[9]);
      //确认支付
      $cpostData = [
        "charset"=>"00",
        "orderId"=>$orderNo,
        "version"=>"1.0",
        "creDt"=>date("Ymd"),
        "token"=>$token,
        "merchantId"=>LKL_MerchantId,
        "requestId"=>"modelscVip".time().rand(1000,9999),
        "service"=>"QRCodePaymentCommit",
        "clientIP"=>"127.0.0.1",
        "payChlTyp"=>"ALIPAY",
        "signType"=>"RSA",
        "tradeType"=>"ALIAPP"
      ];
      $cpostDataStr = formatParaMap($cpostData);
      $cmerchantSign = $this->sha256Str($cpostDataStr,$certs);
      if(!$cmerchantSign){
        return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
      }
      $cpostData['merchantSign'] = $cmerchantSign;
      $lklRes = httpCurlQuery(LKL_API_URL,$cpostData);
      $this->outData['data'] = lalDataToArr($lklRes);

      return $this->fjson($this->outData);*/
    }

    //联系模特订单
    public function contactModelOrder(){
//      $this->param['token'] = '7002966owa9bly9bmpbaqe399711';
//      $this->param['payType'] = 2;
//      $this->param['mid'] = 3;
      $needParam = array(
        'token'=>array('msg'=>'token参数异常','type'=>'str'),
        'payType'=>array('msg'=>'payType参数异常','type'=>'int'),
        'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
      );
      $this->regArguments($needParam,$this->param);
      if($this->outData['code']){
        return $this->fjson($this->outData);
      }
      $uid = $this->getUidByToken($this->param['token']);
      if(!$uid){
        return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
      }
      if($this->param['payType']!=2){
        return $this->fjson(['code'=>405,'msg'=>'目前仅支持支付宝支付']);
      }

      $mModel = new ModelModel();
      $mData = $mModel->findData(['mid'=>$this->param['mid']]);
      if(!$mData){
        return $this->fjson(['code'=>405,'msg'=>'模特信息不存在']);
      }
      //查询自定义价格
      $mbModel = new ModelBalanceModel();
      $mbData = $mbModel->findData(['uid'=>$mData['uid']]);
      if($mbData){
        $price = $mbData['balance'];
      }else{
        $scModel = new SysConfigModel();
        $scData = $scModel->findData(['param_key'=>'MODEL_INFO_BASE_PRICE']);
        $price = $scData['param_value'];
      }
      //生成订单
      $orderNo = buildOrderNo();
      $orderData = [
        'order_no'=>$orderNo,
        'uid'=>$uid,
        'order_money'=>$price,
        'create_time'=>date("YY-mm-dd H:i:s"),
        'order_type'=>2,
        'ext'=>$mData['uid'],
      ];
      $oModel = new OrderModel();
      $oModel->insertData($orderData);

      /*生成支付参数*/
      //预下单
      $orderDetail = [];
      $orderDetail[] = [
        "productDesc"=>"modelsContact",
        "orderSeqNo"=>"002",
        "productId"=>"101",
        "rcvMerchantId"=>LKL_MerchantId,
        "detailOrderId"=>$orderNo,
        "rcvMerchantIdName"=>"Models",
        "showUrl"=>"http://127.0.0.1/modelsInfo",
        "orderAmt"=>($price*100)."",
        "shareFee"=>"0",
        "productName"=>"modelsContact"
      ];
      $orderDetailJson = json_encode($orderDetail,JSON_UNESCAPED_SLASHES);
      $postData = [
        "charset"=>"00",
        "validNum"=>"15",
        "orderId"=>$orderNo,
        "version"=>"1.0",
        "splitType"=>"1",
        "totalAmount"=>($price*100)."",
        "orderTime"=>date("YmdHis"),
        "validUnit"=>"00",
        "merchantId"=>LKL_MerchantId,
        "requestId"=>"modelsContact".time().rand(1000,9999),
        "service"=>"EntOffLinePayment",
        "clientIP"=>"127.0.0.1",
        "signType"=>"RSA",
        "currency"=>"CNY",
        "orderDetail"=>$orderDetailJson,
        "offlineNotifyUrl"=>LKL_ASYNC_URL,
        "tradeType"=>"ALIPAYAPP"
      ];
      $postDataStr = formatParaMap($postData);
      $certs = $this->getPkey();
      if(!$certs){
        return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
      }
      $merchantSign = $this->sha256Str($postDataStr,$certs);
      if(!$merchantSign){
        return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
      }
      $postData['merchantSign'] = $merchantSign;
      $res = httpCurlQuery(LKL_API_URL,$postData);
      $this->outData['data'] = lalDataToArr($res);
      return $this->fjson($this->outData);
    }
    //模特报名参赛订单
    public function modelEnrollOrder(){
//      $this->param['token'] = '7002966owa9bly9bmpbaqe399711';
//      $this->param['payType'] = 2;
//      $this->param['mid'] = 3;
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'payType'=>array('msg'=>'payType参数异常','type'=>'int'),
            'mid'=>array('msg'=>'mid参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uid = $this->getUidByToken($this->param['token']);
        if(!$uid){
            return $this->fjson(['code'=>405,'msg'=>'用户登录信息失效']);
        }
        if($this->param['payType']!=2){
            return $this->fjson(['code'=>405,'msg'=>'目前仅支持支付宝支付']);
        }

        //赛事详情
        $mModel = new MatchModel();
        $mData = $mModel->findData(['mid'=>$this->param['mid']]);

        if(strtotime($mData['edate'])+86400<=time()){
            return $this->fjson(['code'=>401,'msg'=>'报名已结束']);
        }
        $mmModel = new MatchModelModel();
        $mmData = $mmModel->findData(['uid'=>$uid,'mid'=>$this->param['mid']]);
        if($mmData){
            return $this->fjson(['code'=>401,'msg'=>'您已报过名了！']);
        }
        //模特是否认证
        $modelModel = new ModelModel();
        $modelData = $modelModel->findData(['uid'=>$uid]);
        if(!$modelData){
            return $this->fjson(['code'=>401,'msg'=>'请先提交模特认证信息！']);
        }elseif($modelData && $modelData['check_status']!=2){
            return $this->fjson(['code'=>401,'msg'=>'您的认证信息还在审核中！']);
        }
        //生成订单
        $price = $mData['enroll_price'];
        $orderNo = buildOrderNo();
        $orderData = [
            'order_no'=>$orderNo,
            'uid'=>$uid,
            'order_money'=>$price,
            'create_time'=>date("YY-mm-dd H:i:s"),
            'order_type'=>3,
            'ext'=>$this->param['mid'],
        ];
        $oModel = new OrderModel();
        $oModel->insertData($orderData);

        /*生成支付参数*/
        //预下单
        $orderDetail = [];
        $orderDetail[] = [
            "productDesc"=>"modelsEnroll",
            "orderSeqNo"=>"003",
            "productId"=>"102",
            "rcvMerchantId"=>LKL_MerchantId,
            "detailOrderId"=>$orderNo,
            "rcvMerchantIdName"=>"Models",
            "showUrl"=>"http://127.0.0.1/modelsEnroll",
            "orderAmt"=>($price*100)."",
            "shareFee"=>"0",
            "productName"=>"modelsEnroll"
        ];
        $orderDetailJson = json_encode($orderDetail,JSON_UNESCAPED_SLASHES);
        $postData = [
            "charset"=>"00",
            "validNum"=>"15",
            "orderId"=>$orderNo,
            "version"=>"1.0",
            "splitType"=>"1",
            "totalAmount"=>($price*100)."",
            "orderTime"=>date("YmdHis"),
            "validUnit"=>"00",
            "merchantId"=>LKL_MerchantId,
            "requestId"=>"modelsEnroll".time().rand(1000,9999),
            "service"=>"EntOffLinePayment",
            "clientIP"=>"127.0.0.1",
            "signType"=>"RSA",
            "currency"=>"CNY",
            "orderDetail"=>$orderDetailJson,
            "offlineNotifyUrl"=>LKL_ASYNC_URL,
            "tradeType"=>"ALIPAYAPP"
        ];
        $postDataStr = formatParaMap($postData);
        $certs = $this->getPkey();
        if(!$certs){
            return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
        }
        $merchantSign = $this->sha256Str($postDataStr,$certs);
        if(!$merchantSign){
            return $this->fjson(['code'=>405,'msg'=>'请求失败，请稍候重试！']);
        }
        $postData['merchantSign'] = $merchantSign;
        $res = httpCurlQuery(LKL_API_URL,$postData);
        $this->outData['data'] = lalDataToArr($res);
        return $this->fjson($this->outData);
    }

    //支付成功拉卡拉异步回调
    public function lklAsync(){
      $file = STATIC_FILE_DIR.date("YYmmddHis").".txt";
      file_put_contents($file,json_encode($_POST));
      return $this->fjson(["result"=>"false"]);
    }

    //获取加密私钥
    public function getPkey(){
      $certs = array();
      $p12File = BASEDIR.'/App/Common/Conf/lkl.p12';
      $psd = '090857';
      openssl_pkcs12_read(file_get_contents($p12File), $certs, $psd);
      if(!$certs){
        return false;
      }
      return $certs;
    }
    //SHA256加密
    public function sha256Str($str,$certs){
      if (openssl_sign($str, $binarySignature, $certs['pkey'], OPENSSL_ALGO_SHA256)) {
        $merchantSign = strtoupper(bin2hex($binarySignature));
        return $merchantSign;
      }else{
        return false;
      }
    }

    public function agreeInfo(){
//        $this->param['type'] = '1';
        $needParam = array(
            'type'=>array('msg'=>'type参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $aModel = new ModelsAgreeModel();
        $aInfo = $aModel->findData(['type'=>$this->param['type']]);
        $this->outData['data'] = $aInfo['info'];
        $this->outData['title'] = $aInfo['title'];
        return $this->fjson($this->outData);
    }

}

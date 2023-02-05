<?php
/**
 * User: Hufeng
 * Date: 2017/12/04 17:51
 * Desc: Models-App api
 */
namespace App\Controller;

use App\Common\Util\Captchaer;
use App\Common\Util\HashId;
use App\Common\Util\Redis;
use App\Common\Util\Sms;
use App\Model\NoticeModel;
use App\Model\UrlModel;
use App\Model\UserModel;

class Api extends Base {
    public function __construct(){
        parent::__construct();
    }

    //管理后台登录
    public function adminLogin(){
        $uModel = new UserModel();
        $uData = $uModel->findData(['user_name'=>$this->param['userName'],'type'=>1]);
        if($uData['password'] != md5($this->param['passWord'])){
          return $this->fjson(['code'=>401,'msg'=>'密码不正确']);
        }
//        $hashId = new HashId();
//        $token = $hashId->buildAppsecret($uData['uid']);
        $this->outData['data'] = [
          'userName'=>$this->param['userName'],
          'token'=>$uData['open_id']
        ];
        return $this->fjson($this->outData);
    }
    public function editPwd(){
        $needParam = array(
            'token'=>array('msg'=>'token参数异常','type'=>'str'),
            'password'=>array('msg'=>'password参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uModel->editData(['open_id'=>$this->param['token']],['password'=>md5($this->param['password'])]);
        return $this->fjson($this->outData);
    }
    //仪表盘数据（用户数，网址数，点击数）
    public function adminAnaly(){
        $userModel = new UserModel();
        $urlModel = new UrlModel();

        //总数
        $userNum = $userModel->getTotalNum([]);
        $this->outData['data']['userNum'] = $userNum ? $userNum : 0;
        $urlNum = $urlModel->getTotalNum([]);
        $this->outData['data']['urlNum'] = $urlNum ? $urlNum : 0;
        $this->redis = new Redis();
        $totalViewNums = $this->redis->client->get(TOTAL_VIEW_NUMS);
        $this->outData['data']['viewNum'] = $totalViewNums ? $totalViewNums : 0;

        //最新10条url
        $newUrl = $urlModel->queryData(['status'=>1,'ORDER'=>['url_id'=>'DESC'],'LIMIT'=>[0,10]]);
        //最热10条url
        $hotUrl = $urlModel->queryData(['status'=>1,'ORDER'=>['view_num'=>'DESC'],'LIMIT'=>[0,10]]);
        $this->outData['data']['newUrl'] = $newUrl;
        $this->outData['data']['hotUrl'] = $hotUrl;
        $this->outData['data']['baseUrl'] = DOMAIN_URL;
        return $this->fjson($this->outData);
    }
    public function clearjhkdjfsh(){
        //清除数据
        $this->redis = new Redis();
        $this->redis->client->set(TOTAL_VIEW_NUMS,0);
        $this->redis->client->set(TOTAL_VIEW_NUMS_DATE.'2020-08-14',0);
        $this->redis->client->set(TOTAL_VIEW_NUMS_DATE.'2020-08-15',0);
        $this->redis->client->set(TOTAL_VIEW_NUMS_DATE.'2020-08-16',0);
        $urlModel = new UrlModel();
        $urlModel->editData(['url_id[>]'=>0],['view_num'=>0]);
        return $this->fjson($this->outData);
    }
    public function sdfsdf(){
        $uModel = new UrlModel();
        $uList = $uModel->queryData([]);
        foreach($uList as $val){
            $uModel->editData(['url_id'=>$val['url_id']],['url'=>urldecode($val['url'])]);
        }
        return $this->fjson($this->outData);
    }
    //仪表盘数据（最近20天每天用户数，每天网址数，每天点击数）
    public function adminAnalyLine(){
        $userModel = new UserModel();
        $urlModel = new UrlModel();
        $max = 20;
        $dateArr = bulidDateArr($max);

        /*最近20天，每天数量*/
        //每天用户数
        $userNumSql = 'SELECT create_time,COUNT(1) AS nums FROM `url` WHERE create_time>='.$dateArr[$max-1].' GROUP BY create_time';
        $userDate = $userModel->dbConn->query($userNumSql)->fetchAll();
        $userDateArr = [];
        foreach ($userDate as $val){
            $userDateArr[$val['create_time']] = $val['nums'];
        }
        //每天网址数
        //SELECT create_date,COUNT(1) AS nums FROM `url` WHERE create_date>='2020-08-02' GROUP BY create_date
        $urlNumSql = 'SELECT create_date,COUNT(1) AS nums FROM `url` WHERE create_date>='.$dateArr[$max-1].' GROUP BY create_date';
        $urlDate = $urlModel->dbConn->query($urlNumSql)->fetchAll();
        $urlDateArr = [];
        foreach ($urlDate as $val){
            $urlDateArr[$val['create_date']] = $val['nums'];
        }


        //每天点击数(从redis总获取)，组装数据
        $view = [];
        $url = [];
        $user = [];
        $this->redis = new Redis();
        foreach ($dateArr as $val){
            $userNum = isset($userDateArr[$val]) ? $userDateArr[$val] : 0;
            $user[] = $userNum;
            $urlNum = isset($urlDateArr[$val]) ? $urlDateArr[$val] : 0;
            $url[] = $urlNum;
            $viewNum = $this->redis->client->get(TOTAL_VIEW_NUMS_DATE.$val);
            $viewNum = $viewNum ? $viewNum : 0;
            $view[] = $viewNum;
        }
        $this->outData['data']['date'] = $dateArr;
        $this->outData['data']['view'] = $view;
        $this->outData['data']['url'] = $url;
        $this->outData['data']['user'] = $user;
        return $this->fjson($this->outData);

    }

    //用户端仪表盘数据（网址数，点击数）
    public function userAnaly(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uid = $uData['uid'];
        $urlModel = new UrlModel();

        //网址总数
        $this->outData['data']['todayUrlNum'] = intval($urlModel->getTotalNum(['uid'=>$uid,'create_time[>]'=>date('Y-m-d')]));
        $this->outData['data']['lastdayUrlNum'] = intval($urlModel->getTotalNum(['uid'=>$uid,'create_time[>=]'=>date('Y-m-d',strtotime("-1 day")),'create_time[<]'=>date('Y-m-d')]));
        $this->outData['data']['totalUrlNum'] = intval($urlModel->getTotalNum(['uid'=>$uid]));

        $this->redis = new Redis();
        $this->outData['data']['todayViewNum'] = intval($this->redis->client->get(USER_TOTAL_VIEW_NUMS_DATE.$uid.'-'.date('Y-m-d')));
        $this->outData['data']['lastdayViewNum'] = intval($this->redis->client->get(USER_TOTAL_VIEW_NUMS_DATE.$uid.'-'.date('Y-m-d',strtotime("-1 day"))));
        $this->outData['data']['totalViewNum'] = intval($this->redis->client->get(USER_TOTAL_VIEW_NUMS.$uid));

        //最新10条url
        $newUrl = $urlModel->queryData(['uid'=>$uid,'status'=>1,'ORDER'=>['url_id'=>'DESC'],'LIMIT'=>[0,10]]);
        //最热10条url
        $hotUrl = $urlModel->queryData(['uid'=>$uid,'status'=>1,'ORDER'=>['view_num'=>'DESC'],'LIMIT'=>[0,10]]);
        $this->outData['data']['newUrl'] = $newUrl;
        $this->outData['data']['hotUrl'] = $hotUrl;
        $this->outData['data']['baseUrl'] = DOMAIN_URL;
        return $this->fjson($this->outData);
    }
    //用户端仪表盘数据（最近20天每天用户数，每天网址数，每天点击数）
    public function userAnalyLine(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uid = $uData['uid'];
        $urlModel = new UrlModel();

        $max = 20;
        $dateArr = bulidDateArr($max);

        /*最近20天，每天数量*/
        //每天网址数
        //SELECT create_date,COUNT(1) AS nums FROM `url` WHERE create_date>='2020-08-02' GROUP BY create_date
        $urlNumSql = 'SELECT create_date,COUNT(1) AS nums FROM `url` WHERE uid = '.$uid.' and create_date>='.$dateArr[$max-1].' GROUP BY create_date';
        $urlDate = $urlModel->dbConn->query($urlNumSql)->fetchAll();
        $urlDateArr = [];
        foreach ($urlDate as $val){
            $urlDateArr[$val['create_date']] = $val['nums'];
        }
        //每天点击数(从redis总获取)，组装数据
        $view = [];
        $url = [];
        $this->redis = new Redis();
        foreach ($dateArr as $val){
            $urlNum = isset($urlDateArr[$val]) ? $urlDateArr[$val] : 0;
            $url[] = $urlNum;
            $viewNum = $this->redis->client->get(USER_TOTAL_VIEW_NUMS_DATE.$uid.'-'.$val);
            $viewNum = $viewNum ? $viewNum : 0;
            $view[] = $viewNum;
        }
        $newDateArr = [];
        foreach ($dateArr as $val){
            $newDateArr[] = substr($val,5);
        }
        $this->outData['data']['date'] = $newDateArr;
        $this->outData['data']['view'] = $view;
        $this->outData['data']['url'] = $url;
        return $this->fjson($this->outData);
    }

    //通告列表
    public function adminNoticeData(){
        $needParam = array(
            'page'=>array('msg'=>'page参数异常','type'=>'int'),
            'limit'=>array('msg'=>'limit参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }

        $nModel = new NoticeModel();
        $nWhere = [
            'LIMIT' => [($this->param['page']-1)*$this->param['limit'], $this->param['limit']],
            'ORDER'=>['nid'=>'DESC']
        ];
        if($this->param['title']){
            $nWhere['title'] = $this->param['title'];
        }
        $nList = $nModel->queryData($nWhere);
        unset($nWhere['LIMIT']);
        unset($nWhere['ORDER']);
        $totalNum = $nModel->getTotalNum($nWhere);
        $this->outData['count'] = $totalNum;
        $this->outData['data'] = $nList;
        return $this->fjson($this->outData);
    }
    //通告修改添加
    public function addNotice(){
        $needParam = array(
            'title'=>array('msg'=>'title参数异常','type'=>'str'),
            'info'=>array('msg'=>'info参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $nModel = new NoticeModel();
        if($this->param['nid']){
            $nModel->editData(['nid'=>$this->param['nid']],['title'=>$this->param['title'],'info'=>$this->param['info'],'create_time'=>$this->timeNow]);
        }else{
            $nModel->insertData(['title'=>$this->param['title'],'info'=>$this->param['info'],'create_time'=>$this->timeNow]);
        }
        return $this->fjson($this->outData);
    }
    //通告删除
    public function delNotice(){
        $needParam = array(
            'nid'=>array('msg'=>'nid参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $nModel = new NoticeModel();
        $nModel->delData(['nid'=>$this->param['nid']]);
        return $this->fjson($this->outData);
    }
    //用户通告列表-100条
    public function userNoticeData(){
        $nModel = new NoticeModel();
        $nWhere = [
            'LIMIT' => 100,
            'ORDER'=>['nid'=>'DESC']
        ];
        $nList = $nModel->queryData($nWhere);
        $this->outData['data'] = $nList;
        return $this->fjson($this->outData);
    }

    //网址分页列表
    public function adminUrlData(){
        $needParam = array(
            'page'=>array('msg'=>'page参数异常','type'=>'int'),
            'limit'=>array('msg'=>'limit参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }

        $uModel = new UrlModel();
        $uWhere = [
            'LIMIT' => [($this->param['page']-1)*$this->param['limit'], $this->param['limit']],
            'ORDER'=>['url_id'=>'DESC']
        ];
        if($this->param['user_name']){
            $uWhere['user_name'] = $this->param['user_name'];
        }
        if($this->param['dwz']){
            $uWhere['dwz'] = $this->param['dwz'];
        }
        if($this->param['status']){
            $uWhere['status'] = $this->param['status'];
        }else{
            $uWhere['status'] = 1;
        }
        if($this->param['del_date']){
            $uWhere['del_date'] = $this->param['del_date'];
        }
        $uList = $uModel->queryData($uWhere);
        foreach($uList as &$val){
            $val['dwz'] = DOMAIN_URL.$val['dwz'];
        }
        unset($uWhere['LIMIT']);
        unset($uWhere['ORDER']);
        $totalNum = $uModel->getTotalNum($uWhere);
        $this->outData['count'] = $totalNum;
        $this->outData['data'] = $uList;
        return $this->fjson($this->outData);
    }
    //删除网址
    public function delUrl(){
        $needParam = array(
            'url_id'=>array('msg'=>'url_id参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UrlModel();
        $uData = $uModel->findData(['url_id'=>$this->param['url_id']]);
        $uModel->editData(['url_id'=>$this->param['url_id']],['status'=>2,'del_date'=>date('Y-m-d')]);
        $this->redis = new Redis();
        $this->redis->client->hmset(DWZ_INFO.$uData['dwz'],['status'=>2]);
        return $this->fjson($this->outData);
    }
    //修改目标网址
    public function editUrl(){
        $needParam = array(
            'url_id'=>array('msg'=>'url_id参数异常','type'=>'int'),
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UrlModel();
        $uData = $uModel->findData(['url_id'=>$this->param['url_id']]);
        $uModel->editData(['url_id'=>$this->param['url_id']],['url'=>$this->param['url']]);
        $this->redis = new Redis();
        $this->redis->client->hmset(DWZ_INFO.$uData['dwz'],['url'=>$this->param['url']]);
        return $this->fjson($this->outData);
    }
    //用户分页列表
    public function userUrlData(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str'),
            'page'=>array('msg'=>'page参数异常','type'=>'int'),
            'limit'=>array('msg'=>'limit参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uid = $uData['uid'];
        $uModel = new UrlModel();
        $uWhere = [
            'uid' => $uid,
            'status' => 1,
            'LIMIT' => [($this->param['page']-1)*$this->param['limit'], $this->param['limit']],
            'ORDER'=>['url_id'=>'DESC']
        ];

        $uList = $uModel->queryData($uWhere);
        foreach($uList as &$val){
            $val['dwz'] = DOMAIN_URL.$val['dwz'];
        }
        unset($uWhere['LIMIT']);
        unset($uWhere['ORDER']);
        $totalNum = $uModel->getTotalNum($uWhere);
        $this->outData['count'] = $totalNum;
        $this->outData['data'] = $uList;
        return $this->fjson($this->outData);
    }

    //用户分页列表
    public function adminUserData(){
        $needParam = array(
            'page'=>array('msg'=>'page参数异常','type'=>'int'),
            'limit'=>array('msg'=>'limit参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }

        $uModel = new UserModel();
        $uWhere = [
            'LIMIT' => [($this->param['page']-1)*$this->param['limit'], $this->param['limit']],
            'ORDER'=>['uid'=>'DESC']
        ];
        if($this->param['user_name']){
            $uWhere['user_name'] = $this->param['user_name'];
        }
        $uWhere['type'] = 2;
        $uList = $uModel->queryData($uWhere);
        $urlModel = new UrlModel();
        foreach($uList as &$ul){
            $urlNums = $urlModel->getTotalNum(['uid'=>$ul['uid'],'status'=>1]);
            $urlClicks = $urlModel->sumData(['uid'=>$ul['uid'],'status'=>1], 'view_num');
            $ul['urlnums'] = $urlNums ? $urlNums : 0;
            $ul['urlclicks'] = $urlClicks ? $urlClicks : 0;
        }
        unset($uWhere['LIMIT']);
        unset($uWhere['ORDER']);
        $totalNum = $uModel->getTotalNum($uWhere);
        $this->outData['count'] = $totalNum;
        $this->outData['data'] = $uList;
        return $this->fjson($this->outData);
    }
    //冻结解冻用户
    public function freeUser(){
        $needParam = array(
            'uid'=>array('msg'=>'uid参数异常','type'=>'int'),
            'status'=>array('msg'=>'status参数异常','type'=>'int')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        if($this->param['status'] == 1){
            $status = 2;
        }else{
            $status = 1;
        }
        $uModel->editData(['uid'=>$this->param['uid']],['status'=>$status]);
        return $this->fjson($this->outData);
    }
    //工具截流数据配置
    public function jlSet(){
        $needParam = array(
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
            'pre'=>array('msg'=>'pre参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_JL_URL,$this->param['url']);
        $this->redis->client->set(ADMIN_JL_PRE,$this->param['pre']);
        return $this->fjson($this->outData);
    }
    public function jlData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_JL_URL);
        $jlPre = $this->redis->client->get(ADMIN_JL_PRE);
        $this->outData['data']['url'] = $jlUrl ? $jlUrl : '';
        $this->outData['data']['pre'] = $jlPre ? $jlPre : '';
        return $this->fjson($this->outData);
    }
    //工具过期数据配置
    public function gqSet(){
        $needParam = array(
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_GQ_URL,$this->param['url']);
        return $this->fjson($this->outData);
    }
    public function gqData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_GQ_URL);
        $this->outData['data']['url'] = $jlUrl ? $jlUrl : '';
        return $this->fjson($this->outData);
    }
    //工具删除数据配置
    public function scSet(){
        $needParam = array(
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_SC_URL,$this->param['url']);
        return $this->fjson($this->outData);
    }
    public function scData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_SC_URL);
        $this->outData['data']['url'] = $jlUrl ? $jlUrl : '';
        return $this->fjson($this->outData);
    }
    //api开关
    public function apiSet(){
        $needParam = array(
            'apimsg'=>array('msg'=>'url参数异常','type'=>'str'),
            'apibool'=>array('msg'=>'pre参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_API_BOOL,$this->param['apibool']);
        $this->redis->client->set(ADMIN_API_MSG,$this->param['apimsg']);
        return $this->fjson($this->outData);
    }
    public function apiData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_API_BOOL);
        $jlPre = $this->redis->client->get(ADMIN_API_MSG);
        $this->outData['data']['apibool'] = $jlUrl ? $jlUrl : 1;
        $this->outData['data']['apimsg'] = $jlPre ? $jlPre : '';
        return $this->fjson($this->outData);
    }
    //未实名数量
    public function wsnNumSet(){
        $needParam = array(
            'wsnnum'=>array('msg'=>'wsnnum参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_WSC_NUM,$this->param['wsnnum']);
        return $this->fjson($this->outData);
    }
    public function wsnNumData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_WSC_NUM);
        $this->outData['data']['wsnnum'] = $jlUrl ? $jlUrl : 0;
        return $this->fjson($this->outData);
    }
    //手机号注册
    public function telRegSet(){
        $needParam = array(
            'telreg'=>array('msg'=>'telreg参数异常','type'=>'int'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $this->redis->client->set(ADMIN_TELREG_BOOL,$this->param['telreg']);
        return $this->fjson($this->outData);
    }
    public function telRegData(){
        $this->redis = new Redis();
        $jlUrl = $this->redis->client->get(ADMIN_TELREG_BOOL);
        $this->outData['data']['telreg'] = $jlUrl ? $jlUrl : 0;
        return $this->fjson($this->outData);
    }
    //工具删除过去30天内未收到任何点击的网址
    public function adminDelUrl(){
        $date = date("Y-m-d H:i:s", strtotime("-30 day"));
        $uModel = new UrlModel();
        $uModel->delData(['view_num'=>0,'create_date[<]'=>$date]);
        return $this->fjson($this->outData);
    }

    //匿名批量短网址+高级短网址
    public function buildDwz(){
        $needParam = array(
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
            'type'=>array('msg'=>'type参数异常','type'=>'int'),//1批量 2高级
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $urlModel = new UrlModel();
        $dateNow = date('Y-m-d');
        $reqArr = [];
        $this->redis = new Redis();
        if($this->param['type'] == 1){
            //批量
            $urlArr = explode("\n",$this->param['url']);
            if(count($urlArr)>50){
                return $this->fjson(['code' => 401, 'msg' => '网址数量超过50条！']);
            }
            foreach ($urlArr as $url){
                if($this->CheckUrl($url)){
                    $dwz = $this->shorturl($url);
                    $urlInsertData = [
                        'dwz'=>$dwz,
                        'url'=>$url,
                        'end_date'=>date("Y-m-d H:i:s",strtotime("+1 day")),
                        'create_date'=>$dateNow,
                        'create_time'=>$this->timeNow,
                        'create_ip'=>$this->get_client_ip(),
                        'type'=>1,
                        'status'=>1,
                        'isSenior'=>0,
                    ];
                    //存入数据库
                    $urlModel->insertData($urlInsertData);
                    //存入redis
                    $this->redis->client->hmset(DWZ_INFO.$dwz,$urlInsertData);
                    $reqArr[] = DOMAIN_URL.$dwz;
                }
            }
        }else{
            //高级
            $url = $this->param['url'];
            if($this->CheckUrl($url)) {
                if ($this->param['dwz']) {
                    $dwzRes = $this->redis->client->hmget(DWZ_INFO . $this->param['dwz'], ['url']);
                    if ($dwzRes[0]) {
                        return $this->fjson(['code' => 401, 'msg' => '短网址已被占用！']);
                    }
                    $dwz = $this->param['dwz'];
                } else {
                    $dwz = $this->shorturl($url);
                }
                $type = 1;
                if ($this->param['pwd']) {
                    $type = 2;//密码方式
                }
                $urlInsertData = [
                    'dwz' => $dwz,
                    'url' => $url,
                    'create_date' => $dateNow,
                    'type' => $type,
                    'isSenior' => 1,//高级
                    'end_date'=>date("Y-m-d H:i:s",strtotime("+1 day")),
                    'create_time'=>$this->timeNow,
                    'create_ip'=>$this->get_client_ip(),
                    'status'=>1,
                ];
                if ($this->param['pwd']) {
                    $urlInsertData['pwd'] = $this->param['pwd'];
                }
                if ($this->param['intro']) {
                    $urlInsertData['intro'] = $this->param['intro'];
                }
                //存入数据库
                $urlModel->insertData($urlInsertData);
                //存入redis
                $this->redis->client->hmset(DWZ_INFO . $dwz, $urlInsertData);
                $reqArr[] = DOMAIN_URL . $dwz;
            }
        }
        if(count($reqArr)>0){
            $this->outData['data'] = $reqArr;
            return $this->fjson($this->outData);
        }else{
            return $this->fjson(['code' => 401, 'msg' => '网址不合法！']);
        }
    }
    //非匿名批量短网址+高级短网址
    public function userBuildDwz(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str'),
            'url'=>array('msg'=>'url参数异常','type'=>'str'),
            'type'=>array('msg'=>'type参数异常','type'=>'int'),//1批量 2高级
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $urlModel = new UrlModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uid = $uData['uid'];
        $username = $uData['user_name'];
        $this->redis = new Redis();
        if($uData['is_real'] != 1){
            //获取非实名认证数量
            $wsmNum = $this->redis->client->get(ADMIN_WSC_NUM);
            $wsmNum = $wsmNum ? $wsmNum : 0;
            if($wsmNum != 0){
                //获取用户短网址总数
                $userUrlNum = $urlModel->getTotalNum(['uid'=>$uid,'status'=>1]);
                if($userUrlNum>=$wsmNum){
                    return $this->fjson(['code' => 401, 'msg' => '您生成的网址数量已超'.$wsmNum.'条，实名后不受显示']);
                }
            }
        }


        $dateNow = date('Y-m-d');
        $reqArr = [];

        if($this->param['type'] == 1){
            //批量
            $urlArr = explode("\n",$this->param['url']);
            if(count($urlArr)>50){
                return $this->fjson(['code' => 401, 'msg' => '网址数量超过50条！']);
            }
            foreach ($urlArr as $url){
                if($this->CheckUrl($url)){
                    $dwz = $this->shorturl($url);
                    $urlInsertData = [
                        'uid'=>$uid,
                        'user_name'=>$username,
                        'dwz'=>$dwz,
                        'url'=>$url,
                        'create_date'=>$dateNow,
                        'create_time'=>$this->timeNow,
                        'create_ip'=>$this->get_client_ip(),
                        'type'=>1,
                        'status'=>1,
                        'isSenior'=>0,
                    ];
                    //存入数据库
                    $urlModel->insertData($urlInsertData);
                    //存入redis
                    $this->redis->client->hmset(DWZ_INFO.$dwz,$urlInsertData);
                    $reqArr[] = DOMAIN_URL.$dwz;
                }
            }
        }else{
            //高级
            $url = $this->param['url'];
            if($this->CheckUrl($url)) {
                if ($this->param['dwz']) {
                    $dwzRes = $this->redis->client->hmget(DWZ_INFO . $this->param['dwz'], ['url']);
                    if ($dwzRes[0]) {
                        return $this->fjson(['code' => 401, 'msg' => '短网址已被占用！']);
                    }
                    $dwz = $this->param['dwz'];
                } else {
                    $dwz = $this->shorturl($url);
                }
                $type = 1;
                if ($this->param['pwd']) {
                    $type = 2;//密码方式
                }
                $urlInsertData = [
                    'uid'=>$uid,
                    'user_name'=>$username,
                    'dwz' => $dwz,
                    'url' => $url,
                    'create_date' => $dateNow,
                    'type' => $type,
                    'isSenior' => 1,//高级
                    'create_time'=>$this->timeNow,
                    'create_ip'=>$this->get_client_ip(),
                    'status'=>1,
                ];
                if ($this->param['pwd']) {
                    $urlInsertData['pwd'] = $this->param['pwd'];
                }
                if ($this->param['intro']) {
                    $urlInsertData['intro'] = $this->param['intro'];
                }
                //存入数据库
                $urlModel->insertData($urlInsertData);
                //存入redis
                $this->redis->client->hmset(DWZ_INFO . $dwz, $urlInsertData);
                $reqArr[] = DOMAIN_URL . $dwz;
            }
        }
        if(count($reqArr)>0){
            $this->outData['data'] = $reqArr;
            return $this->fjson($this->outData);
        }else{
            return $this->fjson(['code' => 401, 'msg' => '网址不合法！']);
        }
    }
    //api调用接口Get方式
    public function build(){
        $dateNow = date('Y-m-d');
        $url = urldecode($_GET['url']);
        $key = $_GET['key'];
        if(!$this->CheckUrl($url)){
            return $this->fjson(['error'=>410,"msg"=>"url不合法"]);
        }

        $this->redis = new Redis();
        $apiBool = $this->redis->client->get(ADMIN_API_BOOL);
        $apiMsg = $this->redis->client->get(ADMIN_API_MSG);
        $apiMsg = $apiMsg ? $apiMsg : 'api接口已关闭';
        if(!$apiBool){
            return $this->fjson(['error'=>410,"msg"=>$apiMsg]);
        }


        $uModel = new UserModel();
        $uData = $uModel->findData(['api_key'=>$key]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uid = $uData['uid'];
        $username = $uData['user_name'];

        $format = $_GET['format'];
        $dwz = $this->shorturl($url);

        $urlInsertData = [
            'uid'=>$uid,
            'user_name'=>$username,
            'dwz'=>$dwz,
            'url'=>$url,
            'create_date'=>$dateNow,
            'type'=>3,
            'isSenior'=>0,
            'create_time'=>$this->timeNow,
            'create_ip'=>$this->get_client_ip(),
            'status'=>1,
        ];

        //存入数据库
        $urlModel = new UrlModel();
        $urlModel->insertData($urlInsertData);
        //存入redis
        $this->redis->client->hmset(DWZ_INFO.$dwz,$urlInsertData);
        if($format == 'json'){
            return $this->fjson(['error'=>0,"short"=>DOMAIN_URL.$dwz]);
        }else if($format == 'jsonp'){
            $callback = $_GET['callback'];
            return $callback.'('.json_encode(['error'=>0,"short"=>DOMAIN_URL.$dwz],JSON_UNESCAPED_UNICODE).')';
        }else{
            return DOMAIN_URL.$dwz;
        }
    }

    //网址跳转
    public function redirectUrl(){
        $dwz = $_GET['dwz'];
        $this->redis = new Redis();
        $dwzRes = $this->redis->client->hmget(DWZ_INFO.$dwz,['url','pwd','uid','status','end_date']);
        if(!$dwzRes[0]){
            header('Location:'.DOMAIN_URL.'sorry.html');
        }else{
            //是否删除
            if($dwzRes[3]==2){
                $jlUrl = $this->redis->client->get(ADMIN_SC_URL);
                $jlUrlArr = explode("\n",$jlUrl);
                $random_keys=array_rand($jlUrlArr,1);
                $lurl = $jlUrlArr[$random_keys] ? $jlUrlArr[$random_keys] : DOMAIN_URL;
                header('Location:'.$lurl);exit;
            }
            //是否过期
            if($dwzRes[4]){
                if(strtotime($dwzRes[4])<time()){
                    $jlUrl = $this->redis->client->get(ADMIN_GQ_URL);
                    $jlUrlArr = explode("\n",$jlUrl);
                    $random_keys=array_rand($jlUrlArr,1);
                    $lurl = $jlUrlArr[$random_keys] ? $jlUrlArr[$random_keys] : DOMAIN_URL;
                    header('Location:'.$lurl);exit;
                }
            }

            //用户是否被删除
            $uid = 0;
            if($dwzRes[2]){
                $uid = $dwzRes[2];
                $userModel = new UserModel();
                $uData = $userModel->findData(['uid'=>$uid]);
                if($uData['status']!=1){
                    $jlUrl = $this->redis->client->get(ADMIN_SC_URL);
                    $jlUrlArr = explode("\n",$jlUrl);
                    $random_keys=array_rand($jlUrlArr,1);
                    $lurl = $jlUrlArr[$random_keys] ? $jlUrlArr[$random_keys] : DOMAIN_URL;
                    header('Location:'.$lurl);exit;
                }
            }

            //随机跳转比例
            $jlPre = $this->redis->client->get(ADMIN_JL_PRE);
            $randNum = rand(1,100);
            if($randNum<=$jlPre){
                $jlUrl = $this->redis->client->get(ADMIN_JL_URL);
                $jlUrlArr = explode("\n",$jlUrl);
                $random_keys=array_rand($jlUrlArr,1);
                header('Location:'.$jlUrlArr[$random_keys]);
            }else{
                if($dwzRes[1]){
                    //跳转密码网址
                    header('Location:'.PWD_URL."?dwz=".$dwz);
                }else{
                    $this->addViewNums($dwz,$uid);
                    $preUrl = substr(urldecode($dwzRes[0]),0,4);
                    if($preUrl == 'http'){
                        $nurl = urldecode($dwzRes[0]);
                    }else{
                        $nurl = 'http://'.urldecode($dwzRes[0]);
                    }
                    header('Location:'.$nurl);

                }
            }
        }

    }
    //验证密码跳转
    public function checkPwd(){
//        $this->param['dwz'] = 'aBepu1';
//        $this->param['pwd'] = '123';
        $needParam = array(
            'dwz'=>array('msg'=>'dwz参数异常','type'=>'str'),
            'pwd'=>array('msg'=>'pwd参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $dwzRes = $this->redis->client->hmget(DWZ_INFO.$this->param['dwz'],['pwd','url','uid']);
        if($dwzRes[0] == $this->param['pwd']){
            $this->addViewNums($this->param['dwz'],$dwzRes[2]);
            $preUrl = substr(urldecode($dwzRes[1]),0,4);
            if($preUrl == 'http'){
                $nurl = urldecode($dwzRes[1]);
            }else{
                $nurl = 'http://'.urldecode($dwzRes[1]);
            }
            $this->outData['url'] = $nurl;
            return $this->fjson($this->outData);
        }else{
            return $this->fjson(['code'=>401,'msg'=>'密码不正确']);
        }
    }
    //加访问次数(总数，每天，mysql)
    private function addViewNums($dwz,$uid){
        $date = date('Y-m-d');
        $urlModel = new UrlModel();
        $urlModel->editData(['dwz'=>$dwz],['view_num[+]'=>1]);
        $this->redis = new Redis();
        $this->redis->client->incr(TOTAL_VIEW_NUMS);
        $this->redis->client->incr(TOTAL_VIEW_NUMS_DATE.$date);

        if($uid){
            $this->redis->client->incr(USER_TOTAL_VIEW_NUMS.$uid);
            $this->redis->client->incr(USER_TOTAL_VIEW_NUMS_DATE.$uid.'-'.$date);
        }
    }


    /*短网址算法*/
    private function shorturl($url){
        $url=crc32($url);
        $result=sprintf("%u",$url);
        return $this->code62($result);
    }
    private function code62($x){
        $show='';
        while($x>0){
            $s=$x % 62;
            if ($s>35){
                $s=chr($s+61);
            }elseif($s>9&&$s<=35){
                $s=chr($s+55);
            }
            $show.=$s;
            $x=floor($x/62);
        }
        return $show;
    }

    private function CheckUrl($url){
        $preUrl = substr(urldecode($url),0,4);
        if($preUrl != 'http'){
            $url = 'http://'.$url;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) !== false){
            if(stristr($url,'.')){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    //获取验证码
    public function getCaptcha(){
        $needParam = array(
            'hashToken'=>array('msg'=>'hashToken参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $captchaer = new Captchaer();
        $captRes = $captchaer->build();
        $this->redis = new Redis();
        $key = CAPTCHA_KEY.$this->param['hashToken'];
        $this->redis->client->set($key,$captRes['code']);
        $this->outData['data'] = $captRes['base64'];
        return $this->fjson($this->outData);
    }
    //注册
    public function register(){
        $needParam = array(
            'user_name'=>array('msg'=>'mobile参数异常','type'=>'str'),
            'password'=>array('msg'=>'smsCode参数异常','type'=>'str'),
            'capt'=>array('msg'=>'capt参数异常','type'=>'str'),
            'hashToken'=>array('msg'=>'hashToken参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $key = CAPTCHA_KEY.$this->param['hashToken'];
        $capt = $this->redis->client->get($key);
        //检测验证码，是否正确
        if(strtolower($capt) != strtolower($this->param['capt'])){
            return $this->fjson(['code' => 401, 'msg' => '验证码不正确！']);
        }
        //检测用户名是否存在
        $uModel = new UserModel();
        $userInfo = $uModel->findData(['user_name'=>$this->param['user_name']]);
        //检测手机号是否存在
        if($userInfo){
            return $this->fjson(['code' => 401, 'msg' => '用户名已存在！']);
        }
        if($this->param['mobile']){
            $userInfo = $uModel->findData(['mobile'=>$this->param['mobile']]);
            //检测手机号是否存在
            if($userInfo){
                return $this->fjson(['code' => 401, 'msg' => '手机号已存在！']);
            }
        }
        $uData = [
            'type' => 2,
            'user_name' => $this->param['user_name'],
            'password' => md5($this->param['password']),
            'create_time' => $this->timeNow,
        ];
        if($this->param['mobile']){
            //验证短信验证码是否正确
            $this->regSmsCode($this->param['mobile'],$this->param['smsCode']);
            if($this->outData['code']){
                return $this->fjson($this->outData);
            }else{
                $uData['mobile'] = $this->param['mobile'];
            }
        }
        $uid = $uModel->insertData($uData);
        $hashId = new HashId();
        $open_id = $hashId->build($uid);
        $api_key = $hashId->buildKey($uid);
        $uModel->editData(['uid'=>$uid],['open_id'=>$open_id,'api_key'=>$api_key]);
        return $this->fjson(['code'=>0,'msg'=>'注册成功']);
    }
    protected function regSmsCode($tel,$code){
        $this->redis = new Redis();
        $rkey = 'SMSCODE-'.$tel;
        $redisGets = $this->redis->client->hmget($rkey,['smsCode','sendTime']);
        $timeNow = time();
        $overTime = $timeNow - $redisGets[1];
        if($overTime<400){
            if($code == $redisGets[0]){
                $this->redis->client->del([$rkey]);
            }else{
                $this->outData = ['code'=>401,'msg'=>'短信验证码错误，请重新输入'];
            }
        }else{
            $this->outData = ['code'=>401,'msg'=>'验证码已失效，请重新获取验证码'];
        }
        return;
    }
    //登录
    public function login(){
        $needParam = array(
            'user_name'=>array('msg'=>'user_name参数异常','type'=>'str'),
            'password'=>array('msg'=>'smsCode参数异常','type'=>'str'),
            'capt'=>array('msg'=>'capt参数异常','type'=>'str'),
            'hashToken'=>array('msg'=>'hashToken参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $this->redis = new Redis();
        $key = CAPTCHA_KEY.$this->param['hashToken'];
        $capt = $this->redis->client->get($key);
        //检测验证码，是否正确
        if(strtolower($capt) != strtolower($this->param['capt'])){
            return $this->fjson(['code' => 401, 'msg' => '验证码不正确！']);
        }
        //检测用户名是否存在
        $uModel = new UserModel();
        $userInfo = $uModel->findData(['user_name'=>$this->param['user_name'],'type'=>2]);
        //检测手机号是否存在
        if(!$userInfo){
            $userInfo = $uModel->findData(['mobile'=>$this->param['user_name'],'type'=>2]);
            if(!$userInfo) {
                return $this->fjson(['code' => 401, 'msg' => '用户名不存在！']);
            }
        }
        //检测密码是否正确
        if($userInfo['password'] != md5($this->param['password'])){
            return $this->fjson(['code' => 401, 'msg' => '密码不正确！']);
        }
        if($userInfo['status'] != 1){
            return $this->fjson(['code' => 401, 'msg' => '账号已被冻结！']);
        }
        $uModel->editData(['uid'=>$userInfo['uid']],['login_time'=>$this->timeNow,'login_ip'=>$this->get_client_ip()]);
        $this->outData['data'] = $userInfo;
        return $this->fjson($this->outData);
    }
    //实名认证
    public function userReal(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str'),
            'real_name'=>array('msg'=>'real_name参数异常','type'=>'str'),
            'card_no'=>array('msg'=>'card_no参数异常','type'=>'str'),
            'card_pic'=>array('msg'=>'card_pic参数异常','type'=>'str'),
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uModel->editData(['uid'=>$uData['uid']],['real_name'=>$this->param['real_name'],'card_no'=>$this->param['card_no'],'card_pic'=>$this->param['card_pic'],'is_real'=>1]);
        return $this->fjson($this->outData);
    }
    //修改密码
    public function editUserPwd(){
        $needParam = array(
            'open_id'=>array('msg'=>'open_id参数异常','type'=>'str'),
            'password'=>array('msg'=>'password参数异常','type'=>'str')
        );
        $this->regArguments($needParam,$this->param);
        if($this->outData['code']){
            return $this->fjson($this->outData);
        }
        $uModel = new UserModel();
        $uData = $uModel->findData(['open_id'=>$this->param['open_id']]);
        if(!$uData){
            return $this->fjson(['code' => 401, 'msg' => '账号异常！']);
        }
        $uModel->editData(['uid'=>$uData['uid']],['password'=>md5($this->param['password'])]);
        return $this->fjson($this->outData);
    }
    //图片上传
    public function uploadImg(){
        $imgUrl = '';
        if($_FILES){
            foreach ($_FILES as $val){
                $date = date('Ymd');
                $time = time();
                $fileDir = BASEDIR.DIRECTORY_SEPARATOR.'Public'.DIRECTORY_SEPARATOR.$date.DIRECTORY_SEPARATOR;
                $fileExt = pathinfo($val['name'], PATHINFO_EXTENSION);
                $fileName = $time.rand(11111,99999).'.'.$fileExt;
                if(!file_exists($fileDir)){
                    mkdir($fileDir);
                }
                move_uploaded_file($val['tmp_name'],$fileDir.$fileName);
                $imgUrl = DOMAIN_URL.'Public/'.$date.'/'.$fileName;
            }
            return json_encode(['code'=>0,'msg'=>'上传成功','data'=>$imgUrl]);
        }
        return json_encode(['code'=>1,'msg'=>'上传失败']);
    }

    /*发送手机验证码*/
    public function smsCode(){
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
        $resq = $sms->smsSend($this->param['mobile'],$code);
        if(stripos($resq,'000000') !== false){
            return $this->fjson($this->outData);
        }else{
            return $this->fjson(['code'=>401,'msg'=>'发送失败']);
        }
    }
}

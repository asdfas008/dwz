<?php
/**
 * User: Hufeng
 * Date: 2017/6/12 11:35
 * Desc: REDIS
 */
namespace App\Common\Util;
use App\Controller\Event;
use Predis\Client;
use SasPHP\Config;

class Redis{
    public $client;
    public function __construct($database='REDISOPTIONS'){
        $confObj = new Config(BASEDIR.'/App/Common/Conf');
        $conf = $confObj->offsetGet('config');
        $config = $conf['REDIS'];
        

        $redisSentinels = $config['REDISSENTINELS'];
        $redisOptions = $config[$database];
        $this->client = new Client($redisSentinels,$redisOptions);
    }
    public function __destruct(){
        $this->client->quit();
    }

    //订阅
    public function subExpiredMessage(){
        $pubsub = $this->client->pubSubLoop();
        $pubsub->subscribe('__keyevent@0__:expired');
        foreach ($pubsub as $message) {
            $payLoadArr = explode('-',$message->payload);
            if($payLoadArr[0] == 'to'){
                $action = $payLoadArr[1];
                $event = new Event();
                if(method_exists($event,$action)) {
                    $event->$action($payLoadArr);
                }
            }
        }
    }
    //发布
    public function pubMessage($channel,$message){
        $this->client->publish($channel,$message);
    }

    /*单次获得锁资格*/
    public function getLock($key){
        if($this->client->setnx($key,1)){
            return true;
        }else{
            return false;
        }
    }
    /*循环获得锁资格,直至获取到*/
    public function getLockEnabel($key,$index=1){
        if($index>200){$this->client->del([$key]);}//避免死循环
        if($this->client->setnx($key,1)){
            return true;
        }else{
            $index++;
            $this->getLockEnabel($key,$index);
        }
        return true;
    }
    /*释放锁*/
    public function removeLock($key){
        $this->client->del([$key]);
    }
}

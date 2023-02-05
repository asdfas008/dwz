<?php
/**
 * User: Hufeng
 * Date: 2015/12/15 17:36
 * Desc: 核心文件
 */
namespace SasPHP;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SasPHP{

    static public function start() {
        
       
        // 注册AUTOLOAD方法
        spl_autoload_register('SasPHP\SasPHP::autoload');
        require_once BASEDIR.'/App/Common/Common/function.php';
        return self::dispatch();
    }

    static public function autoload($class) {
        try {
            if(file_exists(BASEDIR . '/' . str_replace('\\', '/', $class) . '.php')){
                include_once BASEDIR . '/' . str_replace('\\', '/', $class) . '.php';
            }else{
                throw new \Exception($class.' file is not exists');
            }
        }catch (\Exception $e){
            throw new \Exception($class.' file is not exists');
        }
    }

    static public function dispatch(){
        $uri = $_SERVER['REQUEST_URI'];
        $index = stripos($uri,'?');
        if($index){
            $uri = substr($uri,0,$index);
        }
        list($c, $a) = explode('/', trim($uri, '/'));
        $originC = $c;
      
        // $shuzi = substr($_GET['dwz'],-3);
        
        // if(!is_numeric($shuzi)){
        //   header('Location:https://www.baidu.com/');exit;
        //   die;
        // }
        
        $c = ucwords($c);
        return self::exec($c,$a,$originC);
    }

    static public function exec($c,$a,$originC){
        $class = '\\App\\Controller\\'.$c;
        try{
            $obj = new $class();
            if(method_exists($obj,$a)){
                $res =  $obj->$a();
                return $res;
            }else{
                throw new \Exception('method is not exists');
            }
        }catch (\Exception $e){
            $_GET['dwz'] = $originC;
            $c = 'Api';
            $a = 'redirectUrl';
            $class = '\\App\\Controller\\'.$c;
            $obj = new $class();
            $res =  $obj->$a();
            return $res;
//            return json_encode(['code'=>1,'msg'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);
        }
    }
}


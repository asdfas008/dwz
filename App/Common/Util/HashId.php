<?php
/**
 * User: hufeng
 * Date: 2016/12/21
 * Desc: 阿里OSS对象存储
 */
namespace App\Common\Util;

class HashId{

    public function build($id){
        $id = '9'.str_pad($id,19,0,STR_PAD_LEFT);
        $hashids = new \Hashids\Hashids('', 0, '0123456789abcdefghijklmnopqrstuvwxyz');
        return $hashids->encode($id);
    }
    public function buildKey($id){
        $id = '9'.str_pad($id,9,0,STR_PAD_LEFT);
        $hashids = new \Hashids\Hashids('', 0, '0123456789abcdefghijklmnopqrstuvwxyz');
        return $hashids->encode($id);
    }

    public function buildAppsecret($id){
        $id = '9'.str_pad($id,19,0,STR_PAD_LEFT);
        $hashids = new \Hashids\Hashids('', 0, '0123456789abcdefghijklmnopqrstuvwxyz');
        return rand(100000,999999).$hashids->encode($id).rand(100000,999999);
    }
}
<?php
/**
 * User: hufeng
 * Date: 2016/12/21
 * Desc: 阿里OSS对象存储
 */
namespace App\Common\Util;

class AliOss{
    public $ossClient;

    public function __construct(){
        try {
            $this->ossClient = new \OSS\OssClient(ALI_OSS_ACCESSKEYID, ALI_OSS_ACCESSKEYSECRET, ALI_OSS_ENDPOINT);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    /*
     * 上传文件
     * */
    public function putObject($fileName,$content){
        $bucket = ALI_OSS_BUCKET;
        $object = $fileName;
        $content = $content;
        try {
            $this->ossClient->putObject($bucket, $object, $content);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    /*
     * 获取图片
     * 固定宽高，缩略填充,保证不变形
     * */
    public function getImg($img,$w=100,$h=100){
        return ALI_OSS_URL.$img.'?x-oss-process=image/resize,m_pad,h_'.$h.',w_'.$w;
    }
}
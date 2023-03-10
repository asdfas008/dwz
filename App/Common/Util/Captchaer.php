<?php
/**
 * User: hufeng
 * Date: 2016/12/21
 * Desc: 阿里OSS对象存储
 */
namespace App\Common\Util;

use srcker\Captcha;

class Captchaer{
    public $config  = [
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyz',
        // 使用中文验证码
        'useZh'    => false,
        // 使用背景图片
        'useImgBg' => false,
        // 验证码字体大小(px)
        'fontSize' => 40,
        // 是否画混淆曲线
        'useCurve' => false,
        // 是否添加杂点
        'useNoise' => true,
        // 验证码图片高度
        'imageH'   => 0,
        // 验证码图片宽度
        'imageW'   => 0,
        // 验证码位数
        'length'   => 4,
        // 验证码字体，不设置随机获取
        'fontttf'  => '',
        // 背景颜色
        'bg'       => [243, 251, 254],
    ];

    public function build(){
        $captcha = new Captcha($this->config);
        $res = $captcha->entry();
        return $res;
    }
}
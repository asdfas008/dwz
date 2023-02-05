<?php
/**
 * User: Hufeng
 * Date: 2017/6/12 11:35
 * Desc: REDIS
 */
namespace App\Common\Util;

class Image{

    /*图片压缩*/
    public function compress($file){
        $img = new \Intervention\Image\ImageManager();
        $img->make($file)->save($file, 80);
    }

    /*图片裁切*/
    public function cropImg($file,$w,$h,$x,$y,$fw){
        $dirName = dirname($file);
        $baseDir = '/var/www/game';
        $file = $baseDir.$file;
        $Image = new \Intervention\Image\ImageManager();
        $img = $Image->make($file);
        $realWidth = $img->width();
        $prop = 1;
        if($realWidth>$fw){
            $prop = round($realWidth/$fw,2);
        }
        $w= intval($w*$prop);
        $h= intval($h*$prop);
        $x= intval($x*$prop);
        $y= intval($y*$prop);

        $newImg = $img->crop($w,$h,$x,$y);
        $fileExt = pathinfo($file, PATHINFO_EXTENSION);
        $newFile = $dirName.'/'.time().rand(11111,99999).'.'.$fileExt;
        $newImg->save($baseDir.$newFile,80);
        return  $newFile;
    }
}

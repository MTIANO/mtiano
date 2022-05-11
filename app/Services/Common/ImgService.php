<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Services\Common;

use Illuminate\Support\Facades\Cache;

class ImgService
{

    public function getRandImg($msg){
        $file = $this->getFile('/www/wwwroot/weibo/weibo/');
        $num = count($file);
        $num = rand(0,$num);
        return '点击: <a href="'.$file[$num].'">图片</a>';
    }
    
    
    public function uploadImg($msg){
        $file = $this->getFile('/www/wwwroot/weibo/weibo/');
        $num = count($file);
        $num = rand(0,$num);
        $file = str_replace('http://img.czw-mtiano.cn/','/www/wwwroot/weibo/weibo/',$file[$num]);
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.(new CommonService())->getAccessToken().'&type=image';
        $minetype = 'image/png';
        $ch = curl_init();
        $curl_file = curl_file_create($file, $minetype);
        $postData = [
            'media' => $curl_file,
        ];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $upload_result = json_decode($res, true);
        return $upload_result;
    }
    
    
    
    public function getFile($path){
        $file = [];
        $handle = opendir( $path);
        while ( false !== ( $item = readdir( $handle ) ) ) {
            if($item === '头像图片'){
                continue;
            }
            $item_ = explode('.',$item);
            if((count($item_)===2) && in_array($item_[1],['jpg','png','gif','jpeg'])){
                $file[] = str_replace(env('IMG_PATH'),'',env('IMG_URL').$path.$item);
            }
            if(count($item_)===1){
                $file_ = $this->getFile($path.$item.'/');
                if($file_){
                    $file = array_merge($file,$file_);
                }
            }
        }
        return $file;
    }

}

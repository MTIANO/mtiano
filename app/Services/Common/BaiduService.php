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

use App\Models\MtAuth;

class BaiduService
{
    public function auth($cond){
        
        $is_auth = MtAuth::query()->where('name','baidu')->first();
        $auth_data = [
            'name' => 'baidu',
            'access_token' => $cond['access_token'],
            'expires_in' => $cond['expires_in'],
        ];
        
        if($is_auth){
            MtAuth::query()->where('name','baidu')->update($auth_data);
        }else{
            MtAuth::query()->create($auth_data);
        }
        
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file';
        $data = [
            'method' => 'list',
            'access_token' => $cond['access_token'],
            'dir' => '/weibo',
        ];
        $http = new \GuzzleHttp\Client;
        $user = $http->get($url,['query' => $data]);
        $ad = json_decode($user->getBody(),true);
        dump($ad);die;
        
    }
    
    public function upload_all($path){
        $handles = opendir( $path);
        $access_token = MtAuth::query()->where('name','baidu')->value('access_token');
        $BaiduService = (new \App\Services\Api\BaiduService());
        while ( false !== ( $item = readdir( $handles ) ) ) {
            if($item === '头像图片'){
                continue;
            }
            $item_ = explode('.',$item);
            if((count($item_)===2) && in_array($item_[1],['jpg','png','gif','jpeg'])){
                $fp  = fopen($path.$item,"rb");
                $size = filesize($path.$item);
                $md5_list = [];
                $path_list = [];
                $pan_path = '/weibo/'.$item;
                $i = 0;
                while(!feof($fp)){
                    $handle = fopen($path.$item_[0].'_'.$i.'.'.$item_[1],"wb");
                    fwrite($handle,fread($fp,4194304));//切割的块大小 5m
                    fclose($handle);
                    unset($handle);
                    $md5_list[] = md5_file($path.$item_[0].'_'.$i.'.'.$item_[1]);
                    $path_list[] = $path.$item_[0].'_'.$i.'.'.$item_[1];
                    $i++;
                }
                $rel = $BaiduService->precreate($access_token,$pan_path,$size,0,json_encode($md5_list));
                if($rel === false){
                    $this->clear($path_list);
                    return $rel;
                }
                $block_ = true;
                $block_list = [];
                foreach ($rel['block_list'] as &$value){
                    $file = $path_list[$value];
                    $block_ = $BaiduService->upload($access_token,$pan_path,$rel['uploadid'],$value,$file);
                    if($block_ === false){
                        $this->clear($path_list);
                        return $rel;
                    }
                    if(isset($block_['md5'])){
                        $block_list[] = $block_['md5'];
                    }
                    
                }
                $BaiduService->create($access_token,$pan_path,$size,0,json_encode($block_list),$rel['uploadid']);
                $this->clear($path_list);
                sleep(1);
            }
            if(count($item_)===1){
                $this->upload_all($path.$item.'/');
            }
        }
    }
    
    public function clear($path_list){
        foreach ($path_list as $path_v){
            unlink($path_v);
        }
    }

}

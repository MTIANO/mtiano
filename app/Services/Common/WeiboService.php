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

use App\Models\User;
use App\Models\Weibo;
use Illuminate\Support\Facades\Cache;

class WeiboService
{
    public function push(){
        $weibo = Weibo::query()->orderBy('publish_time','desc')->first();
        //$content_base64 = base64_encode($weibo['content']);
        $key = 'weibo_key';
        if(Cache::get($key) === $weibo['id']){
            return '暂无更新';
        }else{
            $url = "http://pushplus.hxtrip.com/send";
            $token = env('PUSH_TOKEN');
            //$service_id = env('QIYEWEIXIN_MSG_SERVICEID');
            $title = "微博更新提醒";
            
            $name = User::query()->where('id',$weibo['user_id'])->value('nickname');
            $weibo_url = 'https://weibo.com/'.$weibo['user_id'].'/'.$weibo['id'];
            $content = $name.'于'.$weibo['publish_time'].'更新了微博:'.$weibo['content'].'点击: <a href="'.$weibo_url.'">查看</a>';
            
            $doc_type = "html";
            $data = [
                'token' => $token,
                //'service_id' => $service_id,
                'title' => $title,
                'content' => $content,
                'template' => $doc_type
            ];
    
            $http = new \GuzzleHttp\Client;
            $rel = $http->post($url,['form_params' => $data]);
            $rel = json_decode((string)$rel->getBody(), true);
            Cache::forget($key);
            Cache::add($key,$weibo['id']);
            return '发送成功';
            /*if($rel['code'] === 200){
                //Cache::forget($key);
                //Cache::add($key,$weibo['id']);
                return '发送成功';
            }else{
                return $rel['reason'];
            }*/
        }
    }

}

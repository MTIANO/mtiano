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

use App\Models\MtWeiBo;
use App\Models\MtWeiBoUser;
use App\Models\User;
use App\Models\Weibo;
use App\Services\Api\WeiXinService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WeiboService
{
    public function push(){
        $weibo = Weibo::query()->orderBy('publish_time','desc')->first();
        //$content_base64 = base64_encode($weibo['content']);
        $key = 'weibo_key';
        if(Cache::get($key) === $weibo['id']){
            return '暂无更新';
        }else{
            /*$url = "http://pushplus.hxtrip.com/send";
            $token = env('PUSH_TOKEN');
            //$service_id = env('QIYEWEIXIN_MSG_SERVICEID');
            $title = "微博更新提醒";*/
            
            $name = User::query()->where('id',$weibo['user_id'])->value('nickname');
            $weibo_url = 'https://weibo.com/'.$weibo['user_id'].'/'.$weibo['id'];
            //$content = $name.'于'.$weibo['publish_time'].'更新了微博:'.$weibo['content'].'点击: <a href="'.$weibo_url.'">查看</a>';
    
            $rel = (new WeiXinService())->send($name.' 更新了微博',$weibo['content'],$weibo['publish_time'],$weibo_url);
            if($rel === true){
                Cache::forget($key);
                Cache::add($key,$weibo['id']);
            }
            return $rel;
            /*$doc_type = "html";
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
            return '发送成功';*/
            /*if($rel['code'] === 200){
                //Cache::forget($key);
                //Cache::add($key,$weibo['id']);
                return '发送成功';
            }else{
                return $rel['reason'];
            }*/
        }
    }
    
    public function saveWeibo($user_info,$f_value){
        $this->saveUser($user_info);
        $weibo_list = (new \App\Services\Api\WeiBoService())->get_mymblog($f_value);
        if(!is_array($weibo_list)){
            $this->error($weibo_list);
            return false;
        }
    
        foreach ($weibo_list as $value){
            $weibo = MtWeiBo::query()->where('id',$value['id'])->first();
            $original_pictures = [];
            $original_pictures_live = [];
            $video_url = $value['page_info']['media_info']['mp4_720p_mp4'] ?? '';
            if(isset($value['pic_infos']) && $value['pic_infos']){
                foreach ($value['pic_infos'] as $pic_value){
                    $original_pictures[] = $pic_value['mw2000']['url'];
                    if(isset($pic_value['video'])){
                        $original_pictures_live[] = urldecode($pic_value['video']);
                    }
                }
            }
            $data = [
                'id' => $value['id'],
                'user_id' => $value['user']['id'],
                'content' => $value['text_raw'],
                'original_pictures' => implode(',',$original_pictures),
                'original' => 1,
                'video_url' => $video_url,
                'live_url' => implode(',',$original_pictures_live),
                'publish_place' => $value['region_name']??'',
                'publish_time' => date('Y-m-d H:i:s',strtotime($value['created_at'])),
                'publish_tool' => $value['source'],
                'up_num' => $value['attitudes_count'],
                'retweet_num' => $value['reposts_count'],
                'comment_num' => $value['comments_count'],
            ];
            if($weibo){
                MtWeiBo::query()->where('id',$value['id'])->update($data);
            }else{
                MtWeiBo::query()->create($data);
                sleep(1);
                $first = $user_info['screen_name'].'更新了微博';
                $weibo_url = 'https://weibo.com/'.$data['user_id'].'/'.$value['mblogid'];
                (new WeiXinService())->send($first,$data['content'],$data['publish_time'],$weibo_url);
                if($video_url){
                    $video_file = file_get_contents($video_url);
                    $video_name = explode('/',$video_url);
                    $video_name = end($video_name);
                    $video_name = explode('?',$video_name);
                    Storage::disk('weibo')->put($user_info['screen_name'].'/'.$video_name[0], $video_file);
                }
                foreach ($original_pictures as $value_img){
                    $file = file_get_contents($value_img);
                    $name = explode('/',$value_img);
                    $name = end($name);
                    $name = explode('?',$name);
                    Storage::disk('weibo')->put($user_info['screen_name'].'/'.$name[0], $file);
                }
                if($original_pictures_live){
                    foreach ($original_pictures_live as $live_value){
                        $live_file = file_get_contents($live_value);
                        $live_name = explode('/',$live_value);
                        $live_name = end($live_name);
                        $live_name = explode('?',$live_name);
                        Storage::disk('weibo')->put($user_info['screen_name'].'/'.$live_name[0], $live_file);
                    }
                }
            }
        }
    }
    
    public function saveUser($user_info){
        $user = MtWeiBoUser::query()->where('id',$user_info['id'])->first();
        $data = [
            'id' => $user_info['id'],
            'nickname' => $user_info['screen_name'],
            'gender' => $user_info['gender'],
            'location' => $user_info['location'],
            'description' => $user_info['description'],
            'verified_reason' => $user_info['verified_reason']??'',
            'weibo_num' => $user_info['statuses_count'],
            'following' => $user_info['friends_count'],
            'followers' => $user_info['followers_count'],
            'work' => '',
            'education' => '',
            'talent' => '',
            'birthday' => '',
        ];
        if(!$user){
            MtWeiBoUser::query()->create($data);
        }else{
            MtWeiBoUser::query()->where('id',$user_info['id'])->update($data);
        }
    }

}

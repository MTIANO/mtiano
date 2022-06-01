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


use App\Models\MtBogCookie;
use App\Models\MtBogMsg;

class BogService
{
    
    public function bogStart($msg,$user): bool|string
    {
        $data = [
            'user_id' => $user['id'],
            'msg' => $msg['Content'],
            'date' => date('Ymd'),
        ];
        $rel = MtBogMsg::query()->create($data);
        if($rel){
            return 'bog连接成功';
        }
    
        return false;
    }
    
    public function bogEnd($msg,$user): bool|string
    {
        $data = [
            'user_id' => $user['id'],
            'msg' => $msg['Content'],
            'date' => date('Ymd'),
        ];
        $rel = MtBogMsg::query()->create($data);
        if($rel){
            return 'bog断开连接';
        }
        
        return false;
    }
    
    public function bog($msg,$user){
        $data = [
            'user_id' => $user['id'],
            'msg' => $msg['Content'],
            'date' => date('Ymd'),
        ];
        MtBogMsg::query()->create($data);
        
        if($msg['Content'] === '板块'){
            return $this->getForumlist();
        }
        
        if(str_contains($msg['Content'], '板块')){
            $forum_id = explode('-',str_replace('板块','',$msg['Content']));
            $page = $forum_id[1] ?? 1;
            return $this->getForum($forum_id[0],$page);
        }
    
        if(str_contains($msg['Content'], '内容')){
            $thread_id = str_replace('内容','',$msg['Content']);
            return $this->getThread($thread_id);
        }
        
        if(str_contains($msg['Content'], '回复')){
            $thread_id = explode('-',str_replace('回复','',$msg['Content']));
            $page = $thread_id[1] ?? 1;
            return $this->getThreads($thread_id[0],$page);
        }
    
        if($msg['Content'] === '饼干'){
            return $this->getCookieList($user);
        }
    
        if($msg['Content'] === '获取饼干'){
            return $this->getCookie($user);
        }
    
        if(str_contains($msg['Content'], '饼干')){
            $cookie_id = str_replace('饼干','',$msg['Content']);
            return $this->getUserinfo($cookie_id,$user);
        }
    
        if(str_contains($msg['Content'], '签到')){
            $cookie_id = str_replace('签到','',$msg['Content']);
            return $this->sign($cookie_id,$user);
        }
    
        if(str_contains($msg['Content'], '导入')){
            $cookie = str_replace('导入','',$msg['Content']);
            return $this->cookieAdd($cookie,$user);
        }
        
        
        return '无效的bog指令,断开连接请输入bogend';
    }
    
    public function cookieAdd($cookie,$user){
        $cookieadd = $cookie;
        $cookie = explode('#',$cookie);
        $is_cookie = MtBogCookie::query()
            ->where([
                'user_id' => $user['id'],
                'cookie' => $cookie[0],
                'code' => $cookie[1]
            ])->first();
        
        if($is_cookie){
            return '该饼干已导入';
        }
        $userinfo = (new \App\Services\Api\BogService())->cookieAdd($cookieadd);
        if($userinfo['code'] === 3104){
            $data = [
                'user_id' => $user['id'],
                'cookie' => $cookie[0],
                'code' => $cookie[1],
                'date' => date('Ymd'),
            ];
            MtBogCookie::query()->create($data);
            
            return '导入成功';
        }else{
            return $userinfo['info'];
        }
    }
    
    public function sign($cookie_id,$user=[]){
        $cookie = MtBogCookie::query()
            ->where([
                'id' => $cookie_id,
                'user_id' => $user['id'],
            ])->first();
        if(!$cookie){
            return '饼干不存在,请先获取饼干';
        }
        $userinfo = (new \App\Services\Api\BogService())->sign($cookie['cookie'],$cookie['code']);
        
        $text = [];
        if($userinfo['code'] === 7010){
            return '签到成功';
        }elseif ($userinfo['code'] === 7006){
            $text[] = '已签到';
            $text[] = '下次签到时间: '.$userinfo['info']['signtime'];
            $text[] = '积分: '.$userinfo['info']['point'];
            $text[] = '经验: '.$userinfo['info']['exp'];
            return implode('
',$text);
        }else{
            return $userinfo['info'];
        }
    }
    
    public function getUserinfo($cookie_id,$user){
        $cookie = MtBogCookie::query()
            ->where([
                'id' => $cookie_id,
                'user_id' => $user['id'],
            ])->first()->toArray();
        if(!$cookie){
            return '饼干不存在,请先获取饼干';
        }
        $userinfo = (new \App\Services\Api\BogService())->userinfo($cookie['cookie'],$cookie['code']);
        if(!is_array($userinfo)){
            return $userinfo;
        }
    
        $text[] = '饼干详情: '.$userinfo['cookie'];
        $text[] = $userinfo['vip'] ? '是否vip: '.'是' : '是否vip: '.'否';
        $text[] = '是否签到: '.$userinfo['sign'];
        $text[] = '下次签到时间: '.$userinfo['signtime'];
        $text[] = '积分: '.$userinfo['point'];
        $text[] = '经验: '.$userinfo['exp'];
        return implode('
',$text);
    }
    
    
    public function getCookieList($user){
        $cookie_list = MtBogCookie::query()
            ->where([
                'user_id' => $user['id'],
            ])->get()->toArray();
        
        if(!$cookie_list){
            return '暂无饼干';
        }
        $text = [];
        foreach ($cookie_list as $value){
            $text[] = 'id: '.$value['id'].'--'.'cookie: '.$value['cookie'].'--'.'time: '.$value['date'];
        }
        return implode('
',$text);
    }
    
    public function getCookie($user){
        $cookie = (new \App\Services\Api\BogService())->cookieGet();
        if ($cookie['code'] === 2) {
            $cookie['info'] = explode('#',$cookie['info']);
            $is_cookie = MtBogCookie::query()
                ->where([
                    'user_id' => $user['id'],
                    'cookie' => $cookie['info'][0],
                    'code' => $cookie['info'][1]
                ])->first();
            if($is_cookie === null){
                $data = [
                    'user_id' => $user['id'],
                    'cookie' => $cookie['info'][0],
                    'code' => $cookie['info'][1],
                    'date' => date('Ymd'),
                ];
                MtBogCookie::query()->create($data);
            }
            return '领取成功';
        }
        if($cookie['code'] === 2001) {
            return $cookie['info'].'秒后才能领取';
        }
    
        return $cookie['info'];
    }
    
    
    public function getThreads($thread_id,$page): string
    {
        $thread = (new \App\Services\Api\BogService())->thread($thread_id,$page);
        if(!is_array($thread)){
            return $thread;
        }
        $text[] = 'id: '.$thread['id'];
        $text[] = 'time: '.$thread['root'];
        $text[] = 'reply_count: '.$thread['reply_count'];
        $text[] = 'content: '.$thread['content'];
        foreach ($thread['reply'] as $value){
            $content = strip_tags($value['content']);
            $content = str_replace('&gt;','',$content);
            $text[] =  'reply: '.$content;
        }
        return implode('
',$text);
    }
    
    public function getThread($thread_id): string
    {
        $forum = (new \App\Services\Api\BogService())->thread($thread_id);
        if(!is_array($forum)){
            return $forum;
        }
        $text[] = 'id: '.$forum['id'];
        $text[] = 'time: '.$forum['root'];
        $text[] = 'reply_count: '.$forum['reply_count'];
        $text[] = 'content: '.$forum['content'];
        return implode('
',$text);
    }
    
    
    public function getForum($forum_id,$page=1): string
    {
        $forum = (new \App\Services\Api\BogService())->forum($forum_id,$page);
        if(!is_array($forum)){
            return $forum;
        }
        $text = [];
        foreach ($forum as $value){
            $content = mb_substr(strip_tags($value['content']),0,30);
            $text[] =  $value['id'].': '.$content;
        }
        return implode('
',$text);
    }
    
    
    public function getForumlist(): string
    {
        $Forumlist = (new \App\Services\Api\BogService())->forumlist();
        if(!is_array($Forumlist)){
            return $Forumlist;
        }
        $text = [];
        foreach ($Forumlist as $value){
            $text[] = $value['id'].': '.$value['name'];
        }
        return implode('
',$text);
    }

}

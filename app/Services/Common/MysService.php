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
use App\Services\Api\MysService as MysApi;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class MysService
{
    
    protected mixed $cookis;
    protected string $login_ticket;
    protected mixed $con;
    protected mixed $stuid;
    protected mixed $stoken;
    protected array $headers;
    
    public function __construct($con=''){
        $stuid_key = 'stuid_key';
        $stoken_key = 'stoken_key';
        $this->cookis = env('MYS_COOKIE');
        $this->con = $con;
        $this->con->info('开始初始化cookie');
        if(!$this->cookis ){
            $this->con->error('cookie没配置!');
        }
        $cookie_list = explode(';',$this->cookis);
        $this->login_ticket = '';
    
        //获取login_ticket
        foreach ($cookie_list as $cookie_value){
            $cookie_value_ = explode('=',$cookie_value);
            if($cookie_value_[0] === ' login_ticket'){
                $this->login_ticket = $cookie_value_[1];
            }
        }
        if(!$this->login_ticket){
            $this->con->error('获取login_ticket失败!');
            return;
        }
        
        $this->stuid = Cache::get($stuid_key);
        if(!$this->stuid){
            $this->stuid = (new MysApi())->getStuid($this->login_ticket);
            if(is_array($this->stuid)){
                $this->con->error($this->stuid['msg']);
                return;
            }
            Cache::add($stuid_key,$this->stuid);
        }
    
        $this->stoken = Cache::get($stoken_key);
        if(!$this->stoken){
            $this->stoken = (new MysApi())->getStoken($this->login_ticket,$this->stuid);
            if(is_array($this->stoken)){
                $this->con->error($this->stoken['msg']);
                return;
            }
            Cache::add($stoken_key,$this->stoken);
        }
        
        $this->headers = [
            'DS'=> $this->get_ds(false,false),
            'cookie' => 'stuid='.$this->stuid.';stoken='.$this->stoken,
            'x-rpc-client_type' => 2,
            'x-rpc-app_version' => '2.7.0',
            'x-rpc-sys_version' => '6.0.1',
            'x-rpc-channel' => 'mihoyo',
            'x-rpc-device_id' => strtoupper(str_replace('-','',Uuid::uuid3(Uuid::NAMESPACE_URL,$this->cookis)->toString())),
            'x-rpc-device_name' => $this->getrandstr(8),
            'x-rpc-device_model' => 'Mi 10',
            'Referer' => 'https://app.mihoyo.com',
            'Host' => 'bbs-api.mihoyo.com',
            'User-Agent' => 'okhttp/4.8.0',
        ];
        $this->con->info('cookie初始化完毕');
    }
    
    public function AuthSign(){
        $this->con->info('正在获取任务列表');
        $task_list = (new MysApi())->getTaskList($this->headers);
        if(!is_array($task_list)){
            $this->con->error($task_list);
        }
        if($task_list['can_get_points'] === 0){
            $this->con->info('今天已经全部完成了！一共获得'.$task_list['today_total_points'].'个米游币，目前有'.$task_list['total_points'].'个米游币');
            return true;
        }
        //if($task_list['states'][0]['mission_id'] >= 62){
        
        //}
        
        $this->con->info('新的一天，今天可以获得'.$task_list['can_get_points'].'个米游币');
        $this->con->info( '正在签到......');
        $bbs_sign = (new MysApi())->BbsSign($this->headers);
        $this->con->info($bbs_sign);
        sleep(1);
        $this->con->info('正在获取帖子列表......');
        $bbs_list = (new MysApi())->getBbsList($this->headers);
        if(!is_array($bbs_list)){
            $this->con->error($bbs_list);
        }
        $bbs_list_id = array_rand($bbs_list,5);
        foreach ($bbs_list as $k => $value){
            if(isset($value['image_list'][0]['entity_id'])){
                $entity_id = $value['image_list'][0]['entity_id'];
            }
            if(!in_array($k, $bbs_list_id, true)){
                continue;
            }
            $this->con->info('正在看帖子'.$k);
            $read_posts = (new MysApi())->getReadPosts($this->headers,$value['post']['post_id']);
            if($read_posts){
                $this->con->info('帖子'.$k.'浏览成功');
            }
    
            $this->con->info('正在点赞'.$k);
            $read_posts = (new MysApi())->getLikePosts($this->headers,$value['post']['post_id']);
            if($read_posts){
                $this->con->info('点赞帖子'.$k.'成功');
            }
            sleep(1);
        }
        $this->con->info('正在分享帖子');
        $share_posts = (new MysApi())->getSharePosts($this->headers,$bbs_list[0]['post']['subject']);
        if($share_posts){
            $this->con->info('分享帖子'.$bbs_list[0]['post']['subject'].'成功');
        }
        $this->con->info('正在分享帖子');
        $share_posts = (new MysApi())->getSharePosts($this->headers,$entity_id);
        if($share_posts){
            $this->con->info('分享帖子'.$bbs_list[0]['post']['subject'].'成功');
        }
    }
    
    
    public function get_ds($web,$web_old){
        if($web){
            if($web_old){
                $n = env('MIHOYOBBS_SALT_WEB_OLD');
            }else{
                $n = env('MIHOYOBBS_SALT_WEB');
            }
        }else{
            $n = env('MIHOYOBBS_SALT');
        }
        $i = time();
        $r = $this->getrandstr(6);
        $c = md5('salt='.$n.'&t='.$i.'&r='.$r);
        return $i.','.$r.','.$c;
    }
    
    
    public function check_task($con=''){
    
    }
    
    
    function getrandstr($length){
        $str = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);//打乱字符串
        //substr(string,start,length);返回字符串的一部分
        return substr($randStr,0,$length);
    }

}

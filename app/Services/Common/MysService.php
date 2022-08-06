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
use App\Models\MtUser;
use App\Services\Api\MysService as MysApi;
use App\Services\Api\WeiXinService;
use App\Services\Api\YsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class MysService
{
    
    protected mixed $cookis;
    protected string $login_ticket;
    protected mixed $con;
    protected mixed $stuid;
    protected mixed $stoken;
    protected array $headers;
    
    public function __construct($con,$cookie,$user_id){
        $stuid_key = 'stuid_key_'.$user_id;
        $stoken_key = 'stoken_key_'.$user_id;
        //$this->cookis = env('MYS_COOKIE');
        $this->cookis = $cookie;
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
        $this->con->info('cookie初始化完毕');
    }
    
    public function AuthSign($user_id): void
    {
        $this->con->info('原神签到开始');
        $this->ys_sign($user_id);
        $this->con->info('原神签到结束');
        $this->con->info('正在获取任务列表');
        $task_list = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getTaskList();
        if(!is_array($task_list)){
            $this->con->error($task_list);
            return;
        }
        if($task_list['can_get_points'] === 0){
            $this->con->info('今天已经全部完成了！一共获得'.$task_list['today_total_points'].'个米游币，目前有'.$task_list['total_points'].'个米游币');
            return;
        }
        //if($task_list['states'][0]['mission_id'] >= 62){
        
        //}
        $this->con->info('新的一天，今天可以获得'.$task_list['can_get_points'].'个米游币');
        $this->con->info( '正在签到');
        $bbs_sign = (new MysApi($this->stuid,$this->stoken,$this->cookis))->BbsSign();
        $this->con->error($bbs_sign);
        sleep(1);
        $this->con->info('正在获取帖子列表');
        $bbs_list = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getBbsList();
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
            $read_posts = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getReadPosts($value['post']['post_id']);
            if($read_posts){
                $this->con->info('帖子'.$k.'浏览成功');
            }
            $this->con->info('正在点赞'.$k);
            $read_posts = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getLikePosts($value['post']['post_id']);
            if($read_posts){
                $this->con->info('点赞帖子'.$k.'成功');
            }
            sleep(1);
        }
        $this->con->info('正在分享帖子');
        $share_posts = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getSharePosts($bbs_list[0]['post']['subject']);
        if($share_posts){
            $this->con->info('分享帖子'.$bbs_list[0]['post']['subject'].'成功');
        }
        $this->con->info('正在分享帖子');
        $share_posts = (new MysApi($this->stuid,$this->stoken,$this->cookis))->getSharePosts($entity_id);
        if($share_posts){
            $this->con->info('分享帖子'.$bbs_list[0]['post']['subject'].'成功');
        }
    }
    
    public function ys_sign($user_id): bool
    {
        $this->con->info('获取原神账号');
        $account_list = (new YsService($this->stuid,$this->stoken,$this->cookis))->getAccountList();
        if(!is_array($account_list)){
            $this->con->error($account_list);
            return false;
        }
        $this->con->info('正在获取签到奖励列表');
        $checkin_rewards = (new YsService($this->stuid,$this->stoken,$this->cookis))->getCheckinRewards();
        if(!is_array($checkin_rewards)){
            $this->con->error($checkin_rewards);
            return false;
        }
        $rewards = $checkin_rewards['awards'][date('d')-1]['name'].'*'.$checkin_rewards['awards'][date('d')-1]['cnt'];
        $account = $account_list[0];
        $this->con->info('正在为旅行者'.$account['nickname'].'进行签到');
        $is_sign = (new YsService($this->stuid,$this->stoken,$this->cookis))->isSign($account['region'],$account['game_uid']);
        if(!is_array($is_sign)){
            $this->con->error($is_sign);
            return false;
        }
        
        if($is_sign['first_bind']){
            $this->con->error('旅行者'.$account['nickname'].'是第一次绑定米游社，请先手动签到一次');
            return false;
        }
        if($is_sign['is_sign']){
            $first = '米游社每日任务/签到完成通知!!';
            $keyword1 = '旅行者'.$account['nickname'].'今天已经签到过了~今天获得的奖励是:'.$rewards;
            $keyword2 = date('Y-m-d H:i:s');
            $item = MtUser::query()->where('id',$user_id)->value('winxin_id');
            //$this->con->info((new WeiXinService())->send($first,$keyword1,$keyword2,'','',$item));
            $this->con->info('旅行者'.$account['nickname'].'今天已经签到过了~今天获得的奖励是:'.$rewards);
            return true;
        }
        $sign = (new YsService($this->stuid,$this->stoken,$this->cookis))->sign($account['region'],$account['game_uid']);
        
        if($sign === true){
            $first = '米游社每日任务/签到完成通知!!';
            $keyword1 = '已连续签到'.$is_sign['total_sign_day']+1 .'天,今天获得的奖励是'.$rewards;
            $keyword2 = date('Y-m-d H:i:s');
            $item = MtUser::query()->where('id',$user_id)->value('winxin_id');
            $this->con->info((new WeiXinService())->send($first,$keyword1,$keyword2,'','',$item));
            $this->con->info('已连续签到'.$is_sign['total_sign_day']+1 .'天,今天获得的奖励是'.$rewards);
        }else{
            $this->con->error($sign);
            return false;
        }
        return true;
    }
    
    public function ys_user(){
        $asd = (new YsService($this->stuid,$this->stoken,$this->cookis))->get_user_info('cn_gf01','193403219');
        dump($asd);die;
    }

}

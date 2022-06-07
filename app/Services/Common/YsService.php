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

use App\Services\Api\MysService as MysApi;
use App\Services\Api\YsService as YsApi;
use Illuminate\Support\Facades\Cache;

class YsService
{
    protected mixed $cookis;
    protected string $login_ticket;
    protected mixed $con;
    protected mixed $stuid;
    protected mixed $stoken;
    protected array $headers;
    protected string $error = '';
    
    public function __construct(){
        $stuid_key = 'stuid_key';
        $stoken_key = 'stoken_key';
        $this->cookis = env('MYS_COOKIE');
        if(!$this->cookis ){
            $this->error = 'cookie没配置';
            return;
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
            $this->error =  '获取login_ticket失败!';
            return;
        }
        
        $this->stuid = Cache::get($stuid_key);
        if(!$this->stuid){
            $this->stuid = (new MysApi())->getStuid($this->login_ticket);
            if(is_array($this->stuid)){
                $this->error =  $this->stuid['msg'];
                return;
            }
            Cache::add($stuid_key,$this->stuid);
        }
        
        $this->stoken = Cache::get($stoken_key);
        if(!$this->stoken){
            $this->stoken = (new MysApi())->getStoken($this->login_ticket,$this->stuid);
            if(is_array($this->stoken)){
                $this->error =  $this->stoken['msg'];
                return;
            }
            Cache::add($stoken_key,$this->stoken);
        }
    }
    
    public function get_user(){
        if($this->error){
            return $this->error;
        }
        $account_list = (new YsApi($this->stuid,$this->stoken))->getAccountList();
        if(!is_array($account_list)){
            return $account_list;
        }
        $account = $account_list[0];
        $user = (new YsApi($this->stuid,$this->stoken))->get_user_info($account['region'],$account['game_uid']);
        $text = "实时便笺: \r\n";
        $text .= "原粹树脂: ".$user["current_resin"]."/".$user["max_resin"]." (将于".$user["resin_recovery_time"]."秒后全部恢复) \r\n";
        $text .= "洞天财瓮-洞天宝钱: ".$user["current_home_coin"]."/".$user["max_home_coin"]." (将于".$user["home_coin_recovery_time"]."秒后到大储存上限) \r\n";
        $text .= "每日委托任务: ".$user["finished_task_num"]."/".$user["total_task_num"]."  \r\n";
        $text .= "值得铭记的强敌: ".$user["remain_resin_discount_num"]."/".$user["resin_discount_num_limit"]."  \r\n";
        
        if($user['transformer']['recovery_time']['reached']){
            $transformer = "可使用";
        }else{
            $transformer = "冷却中, ".$user['transformer']['recovery_time']['Day']."天".$user['transformer']['recovery_time']['Hour']."小时".$user['transformer']['recovery_time']['Minute']."分后再次使用";
        }
        $text .= "参量质变仪: ".$transformer."  \r\n";
        return $text;
    }

}
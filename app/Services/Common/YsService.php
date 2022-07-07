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

use App\Models\MtYsCookie;
use App\Services\Api\MysService as MysApi;
use App\Services\Api\WeiXinService;
use App\Services\Api\YsService as YsApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class YsService
{
    protected mixed $cookis;
    protected string $login_ticket;
    protected mixed $con;
    protected mixed $stuid;
    protected mixed $stoken;
    protected array $headers;
    protected string $error = '';
    
    public function __construct($user){
        $stuid_key = 'stuid_key_'.$user['id'];
        $stoken_key = 'stoken_key_'.$user['id'];
    
        $this->cookis = MtYsCookie::query()->where('user_id',$user['id'])->value('cookie');
        
        if(!$this->cookis ){
            $this->error = 'cookie没配置, 请参考公众号文章进行配置';
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
        $account_list = (new YsApi($this->stuid,$this->stoken,$this->cookis))->getAccountList();
        if(!is_array($account_list)){
            return $account_list;
        }
        $account = $account_list[0];
        $user = (new YsApi($this->stuid,$this->stoken,$this->cookis))->get_user_info($account['region'],$account['game_uid']);
        $text = "实时便笺: \r\n";
        $text .= "原粹树脂: ".$user["current_resin"]."/".$user["max_resin"]." (将于".(new CommonService())->Sec2Time($user["resin_recovery_time"])."后全部恢复) \r\n";
        $text .= "洞天财瓮-洞天宝钱: ".$user["current_home_coin"]."/".$user["max_home_coin"]." (将于".(new CommonService())->Sec2Time($user["home_coin_recovery_time"])."后到大储存上限) \r\n";
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
    
    public function remind($cookie){
        if($this->error){
            return $this->error;
        }
        $account_list = (new YsApi($this->stuid,$this->stoken,$this->cookis))->getAccountList();
        if(!is_array($account_list)){
            return $account_list;
        }
        $account = $account_list[0];
        $user = (new YsApi($this->stuid,$this->stoken,$this->cookis))->get_user_info($account['region'],$account['game_uid']);
        $text = "原粹树脂: ".$user["current_resin"]."/".$user["max_resin"]." (将于".(new CommonService())->Sec2Time($user["resin_recovery_time"])."后全部恢复) \r\n";
        $text .= "洞天财瓮-洞天宝钱: ".$user["current_home_coin"]."/".$user["max_home_coin"]." (将于".(new CommonService())->Sec2Time($user["home_coin_recovery_time"])."后到大储存上限) \r\n";
        $text .= "每日委托任务: ".$user["finished_task_num"]."/".$user["total_task_num"]."  \r\n";
        $text .= "值得铭记的强敌: ".$user["remain_resin_discount_num"]."/".$user["resin_discount_num_limit"]."  \r\n";
    
        if($user['transformer']['recovery_time']['reached']){
            $transformer = "可使用";
        }else{
            $transformer = "冷却中, ".$user['transformer']['recovery_time']['Day']."天".$user['transformer']['recovery_time']['Hour']."小时".$user['transformer']['recovery_time']['Minute']."分后再次使用";
        }
        $text .= "参量质变仪: ".$transformer."  \r\n";
        
        $redis_key = 'ys_remind_'.$cookie;
        if(!Cache::get($redis_key)){
            if(($user["current_resin"] >= 120) || ($user["current_home_coin"] >= 1000) || $user['transformer']['recovery_time']['reached']){
                $first = '米游社提醒';
                $keyword2 = date('Y-m-d H:i:s');
                dump($text);
                dump((new WeiXinService())->send($first,$text,$keyword2,'','',$cookie));
                Cache::add($redis_key,1,86400);
            }
        }
        
        return true;
    }
    
    public function getGachaLog($user,$msg){
        $page = 1;
        $gacha_type = 301;
        $end_id = 0;
        $query = parse_url($msg['Content'])['query'];
        $query = explode('&',$query);
        $query_all = [];
        foreach ($query as &$value){
            $value_ = explode('=', $value);
            $query_all[$value_[0]] = $value_[1];
        }
        $query_all = array_merge($query_all,['size' => 20,'page' => $page,'gacha_type' => $gacha_type,'end_id' => $end_id]);
        $params = $this->url_encode($query_all);
        $list = (new YsApi())->getGachaLog($params);
        if(!is_array($list)){
            $first = '抽卡记录';
            $keyword2 = date('Y-m-d H:i:s');
            dump((new WeiXinService())->send($first,$list,$keyword2,'','',$user['winxin_id']));
            return $list;
        }
        $log_list = $list;
    
        while ($list){
            $query_all['end_id'] = end($list)['id'];
            $params = $this->url_encode($query_all);
            $list = (new YsApi())->getGachaLog($params);
            if(!is_array($list)){
                $list = false;
            }else{
                $log_list = array_merge($log_list,$list);
            }
            sleep(1);
        }
        //dump(json_encode($log_list,JSON_UNESCAPED_UNICODE));
        $log = array_reverse($log_list);
        $goods = [];
        $goods_ssr = [];
        $num = 0;
        foreach ($log as $key => $log_value){
            $num++;
            if($log_value['rank_type'] == 5){
                $log_value['name'] = $log_value['name'].'_'.$key;
                $goods_ssr[$log_value['name']] = [
                    'count' => isset($goods_ssr[$log_value['name']]['count']) ? $goods_ssr[$log_value['name']]['count']+1 : 1,
                    'num' => $num,
                    'time' => $log_value['time'],
                    'rank_type' => $log_value['rank_type'],
                ];
                $num=0;
            }else{
                $goods[$log_value['name']] = [
                    'count' => isset($goods[$log_value['name']]['count']) ? $goods[$log_value['name']]['count']+1 : 1,
                    'num' => $num,
                    'time' => $log_value['time'],
                    'rank_type' => $log_value['rank_type'],
                ];
            }
        }
        $key = array_column(array_values($goods), 'rank_type');
        array_multisort($key, SORT_DESC, $goods);
        $goods_ssr = array_reverse($goods_ssr);
        $txt = "已".$num."抽未出金 \r\n";
        $txt_ = [];
        foreach ($goods_ssr as $key => $value){
            $key = substr($key, 0, strrpos($key, "_"));
            $txt_[] =$key."(".$value['num'].")";
        }
        $txt .= implode(',',$txt_);
        $first = '抽卡记录';
        $keyword2 = date('Y-m-d H:i:s');
        dump((new WeiXinService())->send($first,$txt,$keyword2,'','',$user['winxin_id']));
        dump($txt);die;
    }
    
    function url_encode($params){
        $tmp = [];
        foreach ($params as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        return implode('&', $tmp);
    }

}

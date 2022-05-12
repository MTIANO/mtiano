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

class MysService
{
    
    protected $cookis;
    protected $login_ticket;
    protected $con;
    protected $stuid;
    protected $stoken;
    protected $headers;
    
    public function __construct($con=''){
        $stuid_key = 'stuid_key';
        $this->cookis = env('MYS_COOKIE');
        $this->con = $con;
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
            return false;
        }
        
        $this->stuid = Cache::get($stuid_key);
        if(!$this->stuid){
            $this->stuid = (new MysApi())->getStuid($this->login_ticket);
            if(is_array($this->stuid)){
                $con->error($this->stuid['msg']);
                return false;
            }
            Cache::add($stuid_key,$this->stuid);
        }
        
        $this->headers = [
            'DS'=> $this->get_ds(false,false),
            'cookie' => 'stuid='.$this->stuid.'stoken=',
        ];
        
        
        dump($this->headers);die;
        
    }
    
    public function AuthSign(){
        
        
        return 123;
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
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);//打乱字符串
        //substr(string,start,length);返回字符串的一部分
        return substr($randStr,0,$length);
    }

}

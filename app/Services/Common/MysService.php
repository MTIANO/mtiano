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

class MysService
{

    public function AuthSign($con=''){
        $cookie = env('MYS_COOKIE');
        if(!$cookie){
            $con->error('cookie没配置!');
        }
        
        $cookie_list = explode(';',$cookie);
        $login_ticket = '';
        
        //获取login_ticket
        foreach ($cookie_list as $cookie_value){
            $cookie_value_ = explode('=',$cookie_value);
            if($cookie_value_[0] === ' login_ticket'){
                $login_ticket = $cookie_value_[1];
            }
        }
        if(!$login_ticket){
            $con->error('获取login_ticket失败!');
            return false;
        }
        
        $stuid = (new MysApi())->getStuid($login_ticket);
        if(is_array($stuid)){
            $con->error($stuid['msg']);
            return false;
        }
        
        dump($stuid);
        die;
        
        return 123;
    }


}

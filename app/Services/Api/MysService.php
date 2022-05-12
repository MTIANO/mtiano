<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;

class MysService
{
    protected string $url = 'http://bog.ac';
    
    public function getStuid($login_ticket){
        $url = env('BBS_COOKIE_URL').'?login_ticket='.$login_ticket;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['data']['msg'] === '成功'){
            return $rel['data']['cookie_info']['account_id'];
        }
        return ['msg' =>$rel['data']['msg'],'info' => $rel['data']['info']];
    }
    
    public function getStoken($login_ticket,$stuid){
        $url = env('BBS_COOKIE_URL2').'?login_ticket='.$login_ticket.'&token_types=3&uid='.$stuid;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['message'] === 'OK'){
            return $rel['data']['list'][0]['token'];
        }
        return ['msg' =>$rel['message']];
    }
    
    public function getTaskList($headers){
        $url = env('BBS_TASKS_LIST');
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers
        ];
        $rel = $http->get($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return $rel['data'];
        
    }

}

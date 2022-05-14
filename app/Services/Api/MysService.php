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
    
    public function getBbsList($headers,$from_id=26){
        $url = env('BBS_LIST_URL').'&forum_id='.$from_id;
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers
        ];
        $rel = $http->get($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return $rel['data']['list'];
    }
    
    public function BbsSign($headers){
        $url = env('BBS_SIGN_URL');
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers
        ];
        $rel = $http->post($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return '签到成功!';
    }
    
    public function getReadPosts($headers,$post_id){
        $url = env('BBS_DETAIL_URL').'?post_id='.$post_id;
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers
        ];
        $rel = $http->get($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
    
    public function getLikePosts($headers,$post_id){
        $url = env('BBS_LIKE_URL');
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers,
            'body' => json_encode([
                'post_id' => $post_id,
                'is_cancel' => false
            ], JSON_THROW_ON_ERROR)
        ];
        $rel = $http->post($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
    
    public function getSharePosts($headers,$entity_id){
        $url = env('BBS_SHARE_URL').'&entity_id='.$entity_id;
        $http = new \GuzzleHttp\Client;
        $headers = [
            'headers' => $headers
        ];
        $rel = $http->get($url,$headers);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
}

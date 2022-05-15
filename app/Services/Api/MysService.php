<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class MysService
{
    protected string $url = 'http://bog.ac';
    
    /**
     * @author  czw
     * @title
     * @date    2022/5/15 22:16
     */
    protected array $headers;
    
    public function __construct($stuid = '',$stoken = ''){
        $this->headers = [
            'DS'=> $this->get_ds(false,false),
            'cookie' => 'stuid='.$stuid.';stoken='.$stoken,
            'x-rpc-client_type' => 2,
            'x-rpc-app_version' => '2.7.0',
            'x-rpc-sys_version' => '6.0.1',
            'x-rpc-channel' => 'mihoyo',
            'x-rpc-device_id' => strtoupper(str_replace('-','',Uuid::uuid3(Uuid::NAMESPACE_URL,env('MYS_COOKIE'))->toString())),
            'x-rpc-device_name' => $this->getrandstr(8),
            'x-rpc-device_model' => 'Mi 10',
            'Referer' => 'https://app.mihoyo.com',
            'Host' => 'bbs-api.mihoyo.com',
            'User-Agent' => 'okhttp/4.8.0',
        ];
    }
    
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
    
    public function getTaskList(){
        $url = env('BBS_TASKS_LIST');
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return $rel['data'];
    }
    
    public function getBbsList($from_id=26){
        $url = env('BBS_LIST_URL').'&forum_id='.$from_id;
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return $rel['data']['list'];
    }
    
    public function BbsSign(){
        $url = env('BBS_SIGN_URL');
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return '签到成功!';
    }
    
    public function getReadPosts($post_id){
        $url = env('BBS_DETAIL_URL').'?post_id='.$post_id;
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
    
    public function getLikePosts($post_id){
        $url = env('BBS_LIKE_URL');
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers,
            'body' => json_encode([
                'post_id' => $post_id,
                'is_cancel' => false
            ], JSON_THROW_ON_ERROR)
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
    
    public function getSharePosts($entity_id){
        $url = env('BBS_SHARE_URL').'&entity_id='.$entity_id;
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return false;
        }
        return true;
    }
    
    public function isSign($headers){
    
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
    
    function getrandstr($length){
        $str = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);//打乱字符串
        //substr(string,start,length);返回字符串的一部分
        return substr($randStr,0,$length);
    }
}

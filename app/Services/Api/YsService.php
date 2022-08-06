<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class YsService
{
    protected string $url = 'http://bog.ac';
    
    /**
     * @author  czw
     * @title
     * @date    2022/5/15 22:16
     */
    protected array $headers;
    
    public function __construct($stuid = '',$stoken = '',$cookis = ''){
        if(!$cookis){
            $cookis = env('MYS_COOKIE');
        }
        $this->headers = [
            'Accept' => 'application/json, text/plain, */*',
            'DS'=> $this->get_ds(true,true),
            'Origin' => 'https://webstatic.mihoyo.com',
            'x-rpc-app_version' => '2.34.1',
            'cookie' => $cookis,
            'x-rpc-client_type' => 5,
            'x-rpc-device_id' => strtoupper(str_replace('-','',Uuid::uuid3(Uuid::NAMESPACE_URL,env('MYS_COOKIE'))->toString())),
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'zh-CN,en-US;q=0.8',
            'X-Requested-With' => 'com.mihoyo.hyperion',
            'Referer' => 'https://webstatic.mihoyo.com/bbs/event/signin-ys/index.html?bbs_auth_required=true&act_id='.env('GENSHIN_ACT_ID').'&utm_source=bbs&utm_medium=mys&utm_campaign=icon',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 9; Unspecified Device) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/39.0.0.0 Mobile Safari/537.36 miHoYoBBS/2.3.0',
        ];
    }
    
    public function getAccountList(){
        $url = env('ACCOUNT_INFO_URL');
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['message'] === 'OK'){
            return $rel['data']['list'];
        }
        return $rel['message'];
    }
    
    public function getCheckinRewards(){
        $url = env('GENSHIN_CHECKIN_REWDRDS');
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['message'] === 'OK'){
            return $rel['data'];
        }
        return $rel['message'];
    }
    
    public function isSign($region,$uid){
        $url = env('GENSHIN_IS_SIGNURL').'?act_id='.env('GENSHIN_ACT_ID').'&region='.$region.'&uid='.$uid;
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers
        ];
        $rel = $http->get($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['message'] === 'OK'){
            return $rel['data'];
        }
        return $rel['message'];
    }
    
    public function sign($region,$uid){
        $url = env('GENSHIN_SIGNURL').'?act_id='.env('GENSHIN_ACT_ID').'&region='.$region.'&uid='.$uid;
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => $this->headers,
            'body' => json_encode([
                'act_id' => env('GENSHIN_ACT_ID'),
                'region' => $region,
                'uid' => $uid
            ], JSON_THROW_ON_ERROR)
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] === 0){
            return true;
        }
        return $rel['message'];
    }
    
    public function get_user_info($server,$role_id){
        $url = 'https://api-takumi-record.mihoyo.com/game_record/app/genshin/api/dailyNote?server='.$server.'&role_id='.$role_id;
        $http = new \GuzzleHttp\Client;
        $this->headers['x-rpc-app_version'] = '2.11.1';
        $this->headers['DS'] = $this->get_os_ds('role_id='.$role_id.'&server='.$server);
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
    
    public function get_ds($web,$web_old){
        if($web){
            $n = env('MIHOYOBBS_SALT_WEB');
        }else{
            $n = env('MIHOYOBBS_SALT');
        }
        $i = time();
        $r = $this->getrandstr(6);
        $c = md5('salt='.$n.'&t='.$i.'&r='.$r);
        return $i.','.$r.','.$c;
    }
    
    public function get_os_ds($query){
        $n = 'xV8v4Qu54lUKrEYFZkJhB8cuOh9Asafs';
        $i = time();
        $r = random_int(100001,200000);
        $b = '';
        $q = $query;
        $c = md5('salt='.$n.'&t='.$i.'&r='.$r.'&b='.$b.'&q='.$q);
        return $i.','.$r.','.$c;
    }
    
    function getrandstr($length){
        $str = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);//打乱字符串
        //substr(string,start,length);返回字符串的一部分
        return substr($randStr,0,$length);
    }
    
    public function getGachaLog($params){
        $url = env('YS_CK_URL').'?'.$params;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['retcode'] !== 0){
            return $rel['message'];
        }
        return $rel['data']['list'];
    }
}

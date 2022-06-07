<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class WeiXinService
{
    
    public function getToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        if(isset($rel['errcode']) && $rel['errcode']){
            return $rel['errmsg'];
        }
        return $rel;
    }
    
    public function send($first,$keyword1,$keyword2,$sand_url,$touser='oERWv6qbxUaXC6Thly0ggeAkVilM'){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $http = new \GuzzleHttp\Client;
        
        $msg = [
            'touser' => $touser,
            'template_id' => 'eUDAM5lz9Sz_zzWzu9UnrHNS6NpIhsbktnl_E7kLJTI',
            'url' => $sand_url,
            'topcolor' => '#FF0000',
            'data' => [
                'first'  => [
                    'value' => $first,
                    'color' => '#173177',
                ],
                'keyword1' =>  [
                    'value' => $keyword1,
                    'color' => '#173177',
                ],
                'keyword2' =>  [
                    'value' => $keyword2,
                    'color' => '#173177',
                ],
                'remark' =>  [
                    'value' => '点击查看内容',
                    'color' => '#173177',
                ],
            ]
        ];
        $data = [
            'body' => json_encode($msg)
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['errcode'] === 0){
            return true;
        }
        return $rel['errmsg'];
    }
    
    public function menu_create(){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $http = new \GuzzleHttp\Client;
        $msg = [
            'button' => [
                'type' => 'click',
                'name' => urlencode('原神'),
                'key' => "YS",
            ]
        ];
        $data = [
            'body' => urldecode(json_encode($msg))
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        dump($rel);die;
        if($rel['errcode'] === 0){
            return true;
        }
        return $rel['errmsg'];
    }
    
    public function menu_del(){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$access_token;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        dump($rel);die;
        if($rel['errcode'] === 0){
            return true;
        }
        return $rel['errmsg'];
    }
    
    
    
}

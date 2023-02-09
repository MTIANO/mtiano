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

    public function send($first,$keyword1,$keyword2,$sand_url = '',$remark = '点击查看内容',$touser='oERWv6qbxUaXC6Thly0ggeAkVilM'){
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
                    'value' => $remark,
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
            'body' => json_encode($msg, JSON_THROW_ON_ERROR)
        ];
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
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
        if($rel['errcode'] === 0){
            return true;
        }
        return $rel['errmsg'];
    }

    public function get_user(){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['data']['openid'];
    }

    public function custom_text($open_id,$text,$type='text'){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
        if($type === 'mpnews'){
            $msg = [
                'touser' => $open_id,
                'msgtype' => 'mpnews',
                'mpnews' => [
                    'media_id' => $text
                ],
            ];
        }else{
            $msg = [
                'touser' => $open_id,
                'msgtype' => 'text',
                'text' => [
                    'content' => $text
                ],
            ];
        }
        $data = [
            'body' => json_encode($msg,JSON_UNESCAPED_UNICODE)
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,$data);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['errcode'] === 0){
            return true;
        }
        return $rel['errmsg'];
    }

    public function draft_add($data){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/draft/add?access_token='.$access_token;

        $post = [
            'articles' => $data
        ];
        $post = [
            'body' => json_encode($post,JSON_UNESCAPED_UNICODE)
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,$post);
        $rel = json_decode((string)$rel->getBody(), true);
        if(isset($rel['errcode']) && $rel['errcode']){
            return false;
        }
        return $rel['media_id'];
    }

    public function batchget_material($type = 'image',$offset = 0,$count=20){
        $access_token = $this->getToken();
        if(!is_array($access_token)){
            return $access_token;
        }
        $access_token = $access_token['access_token'];
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$access_token;
        $post = [
            'type' => $type,
            'offset' => $offset,
            'count' => $count,
        ];
        $post = [
            'body' => json_encode($post,JSON_UNESCAPED_UNICODE)
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,$post);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel;
    }

}

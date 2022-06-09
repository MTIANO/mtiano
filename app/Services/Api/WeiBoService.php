<?php

namespace App\Services\Api;

use Exception;

class WeiBoService
{
    
    public function get_mymblog($user_id,$page=1,$feature=1){
        sleep(random_int(0,3));
        try {
            $url = 'https://weibo.com/ajax/statuses/mymblog?uid='.$user_id.'&page='.$page.'&feature='.$feature;
            $http = new \GuzzleHttp\Client;
            $data = [
                'headers' => [
                    'cookie' => env('WEIBO_COOKIE'),
                    'User-Agent' => 'Apipost client Runtime/+https://www.apipost.cn/'
                ]
            ];
            $rel = $http->get($url,$data);
            $rel = json_decode((string)$rel->getBody(), true);
            if(isset($rel['data']['list']) && $rel['data']['list']){
                return $rel['data']['list'];
            }
            return '微博获取失败!';
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function get_user_info($user_id){
        sleep(random_int(0,3));
        try {
            $url = 'https://weibo.com/ajax/profile/info?uid='.$user_id;
            $http = new \GuzzleHttp\Client;
            $data = [
                'headers' => [
                    'cookie' => env('WEIBO_COOKIE'),
                    'User-Agent' => 'Apipost client Runtime/+https://www.apipost.cn/'
                ]
            ];
            $rel = $http->get($url,$data);
            $rel = json_decode((string)$rel->getBody(), true);
            if(isset($rel['data']['user']) && $rel['data']['user']){
                return $rel['data']['user'];
            }
            return '微博获取失败!';
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }

}

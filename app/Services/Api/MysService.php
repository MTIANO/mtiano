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
        dump($rel);die;
        if($rel['errno'] === 0){
            return $rel;
        }
    
        return false;
    }

}

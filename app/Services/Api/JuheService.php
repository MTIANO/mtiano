<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class JuheService
{
    protected string $url = 'https://tui.juhe.cn/api/plus/pushApi';

    public function push($title,$content,$type=1){
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
             ],
            'form_params' => [
                'token' => env('QIYEWEIXIN_TOKEN'),
                'service_id' => env('QIYEWEIXIN_MSG_SERVICEID'),
                'title' => $title,
                'content' => $content,
                'doc_type' => 'markdown',
            ]
        ];
        $rel = $http->post($this->url,$data);
        return json_decode((string)$rel->getBody(), true);
    }

}

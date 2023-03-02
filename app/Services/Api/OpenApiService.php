<?php

namespace App\Services\Api;

class OpenApiService
{
    public function models(){
        $url = 'https://api.openai.com/v1/models';
        $http = new \GuzzleHttp\Client;
        $data = [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPEN_API'),
                'OpenAI-Organization' => 'org-rMwSHVv29AF0GL1Rev7WMvTn'
            ]
        ];
        $rel = $http->get($url,$data);
        return json_decode((string)$rel->getBody(), true);
    }

    public function completions($Content){
        $url = 'https://api.openai.com/v1/chat/completions';
        $http = new \GuzzleHttp\Client;
        $message[] = [
            'role' => 'user',
            'content' => $Content,
        ];
        $data = [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPEN_API'),
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'model' => 'gpt-3.5-turbo',
                'messages' => $message,
                'temperature' => 0,
                'max_tokens' => 4000,
            ], JSON_THROW_ON_ERROR)
        ];
        $rel = $http->post($url,$data);
        return json_decode((string)$rel->getBody(), true);
    }
}

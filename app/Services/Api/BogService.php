<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;

class BogService
{
    protected string $url = 'http://bog.ac';
    
    public function forumlist(){
        $url = $this->url.'/api/forumlist';
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['code'] === 6001){
            return $rel['info'];
        }
        return $rel['info'];
    }
    
    public function forum($id,$page = 1){
        $url = $this->url.'/api/forum';
        $http = new \GuzzleHttp\Client;
        $data = [
            'id' => $id,
            'page' => $page
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['code'] === 6001){
            return $rel['info'];
        }
        return $rel['info'];
    }
    
    
    public function thread($id,$page = 1){
        $url = $this->url.'/api/threads';
        $http = new \GuzzleHttp\Client;
        $data = [
            'id' => $id,
            'page' => $page,
            'page_def' => 10,
            'order' => 1,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }
    
    public function cookieGet(){
        $url = $this->url.'/post/cookieGet';
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url);
        return json_decode((string)$rel->getBody(), true);
    }
    
    public function userinfo($cookie,$code){
        $url = $this->url.'/api/userinfo';
        $http = new \GuzzleHttp\Client;
        $data = [
            'cookie' => $cookie,
            'code' => $code,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['code'] === 6001){
            return $rel['info'];
        }
        return $rel['info'];
    }
    
    public function sign($cookie,$code){
        $url = $this->url.'/api/sign';
        $http = new \GuzzleHttp\Client;
        $data = [
            'cookie' => $cookie,
            'code' => $code,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        return json_decode((string)$rel->getBody(), true);
    }
    
    public function cookieAdd($cookieadd,$master = 0){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'master' => $master,
            'cookieadd' => $cookieadd,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        return json_decode((string)$rel->getBody(), true);
    }
    
    public function remarks($cookie,$code,$target,$remarks){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'cookie' => $cookie,
            'code' => $code,
            'target' => $target,
            'remarks' => $remarks,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }
    
    public function cookiedel($cookie,$code,$del){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'cookie' => $cookie,
            'code' => $code,
            'del' => $del,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }
    
    public function post($res,$forum,$title,$name,$comment,$cookie,$webapp,$img = []){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'res' => $res,
            'forum' => $forum,
            'title' => $title,
            'name' => $name,
            'comment' => $comment,
            'cookie' => $cookie,
            'webapp' => $webapp,
            'img' => $img,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }
    
    public function del($id,$cookie){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'cookie' => $cookie,
            'id' => $id,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }
    
    public function upload($image){
        $url = $this->url.'/api/cookieAdd';
        $http = new \GuzzleHttp\Client;
        $data = [
            'image' => $image,
        ];
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        return $rel['info'];
    }

}

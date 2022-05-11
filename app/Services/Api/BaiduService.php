<?php

namespace App\Services\Api;


use Illuminate\Support\Facades\Http;

class BaiduService
{
    public function precreate($access_token,$path,$size,$isdir,$block_list){
        dump('预上传开始');
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=precreate&access_token='.$access_token;
        $data = [
            'path' => $path,
            'size' => $size,
            'isdir' => $isdir,
            'block_list' => $block_list,
            'autoinit' => 1,
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['errno'] === 0){
            dump('预上传结束');
            return $rel;
        }
    
        return false;
    }
    
    public function upload($access_token,$path,$uploadid,$partseq,$file){
        $file_ = explode('/',$file);
        dump('上传'.end($file_).'开始');
        $url = 'https://d.pcs.baidu.com/rest/2.0/pcs/superfile2?method=upload&type=tmpfile&access_token='.$access_token.'&path='.$path.'&uploadid='.$uploadid.'&partseq='.$partseq;
        $file = fopen($file, 'rb');
        $rel = Http::attach(end($file_), $file, end($file_))->timeout(0)->post($url);
        $rel = json_decode($rel->Body(), true);
        if(!isset($rel['error_code'])){
            dump('上传'.end($file_).'结束');
            dump($rel);
            return $rel;
        }
    
        return false;
    }
    
    
    public function create($access_token,$path,$size,$isdir,$block_list,$uploadid){
        dump('创建文件开始');
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=create&access_token='.$access_token;
        $data = [
            'path' => $path,
            'size' => $size,
            'isdir' => $isdir,
            'uploadid' => $uploadid,
            'block_list' => $block_list,
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        dump($data);
        if($rel['errno'] === 0){
            dump('创建文件结束');
            return $rel;
        }
        
        return false;
    }
    
    public function file_list($access_token){
        $url = 'https://pan.baidu.com/rest/2.0/xpan/file?method=list&access_token='.$access_token;
        $http = new \GuzzleHttp\Client;
        $rel = $http->get($url);
        $rel = json_decode((string)$rel->getBody(), true);
        dump($rel);
        if($rel['errno'] === 0){
            return $rel;
        }
    
        return false;
    }

}

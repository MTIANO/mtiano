<?php

namespace App\Http\Controllers\Common;


use App\Services\Common\CommonService;
use App\Services\Common\ImgService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WeiXinController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    
    public function index(){
        $data = [
            'title'=>'会员的世界!',
            'ba'=>'备案号:粤ICP备16032172号-3',
        ];
        return view('welcome',$data);
    }
    
    public function test(){
        
        /*$url = 'http://openapi.baidu.com/oauth/2.0/authorize?response_type=token&redirect_uri=http://www.czw-mtiano.cn/Baidu/callback&scope=basic,netdisk&client_id='.env('BAIDU_PAN_APPKEY');
        dump($url);
        $http = new \GuzzleHttp\Client;
        $user = $http->get($url);
        $ad = json_decode($user->getBody(),true);
        dump($ad);die;*/
        
        
        $msg = array (
            'ToUserName' => 'gh_03aa44ccfbb4',
            'FromUserName' => 'oERWv6qbxUaXC6Thly0ggeAkVilM',
            'CreateTime' => '1654050556',
            'MsgType' => 'text',
            'Content' => '签到',
            'MsgId' => '23680307719913567',
            'Encrypt' => 'mHLqor1e0B9JtpF0aAcL4GJ2FbYVYkEwZGcRNSl2Jw95bn802JQr9CdkyvuRsDIJyJ4wMZFz0tRsAmcB8pOzOrkiGraZAtiCD2mMsuWjsSfvbx9/x7ZkRbbuP0ThYWSm6bqsqwS7IBXl/UptdHe5W4AyE/0c+dznsfB+09RkDvBS/DXxtxWGCOkdBBOvDNCYBpr22lyiIXbzp2nksGyfWcvn+ojQ79bc+OnedrJgvtLJlzFdpyxyj1wIkxUx9pZhrFa+ooFkeVwmuLWo/3QoEgF5QSLVuJUMH/a6iu30i3FMvtXsbomxgV3JySf30bTSSp4ZhrlKv7mFU77ya4s2af7MLKzurQeIVAiq4rPrph/cRgG6ZEwiip+aSkg+UuZCSJUDJZpsMOex9RwTuq4VgpmLwkOxMd8M3/D5Q1hL0OU=',
        );
        $CommonService = new CommonService();
        dump($CommonService->manage($msg));
        die;
    }
    
    public function firstValid(Request $request){
        if($request->method() === 'GET'){
            return (new CommonService())->checkSignature($_GET);
        }
        echo $this->responseMsg();
        exit();
    }
    
    public function responseMsg(){
        $CommonService = new CommonService();
        $msg = $CommonService->getMsg();
        if(!$msg){
            return false;
        }
        $CommonService->addUser($msg);
        //Log::channel('daily')->info($msg);
        switch ($msg['MsgType']){
            case'event':
                if($msg['Event'] === 'subscribe' ){
                    $text = '欢迎来到会员的世界!';
                    return $CommonService->doText($msg,$text);
                }
                if($msg['Event'] === 'unsubscribe' ){
                    $CommonService->disableUser($msg);
                    return true;
                }
            case'text':
                $text = $CommonService->manage($msg);
                if($text === false){
                    $text = '指令无效,更多功能指令请联系本人!(目前开放:老黄历, 图片, bog)';
                }elseif($text === true){
                    $text = '操作成功!';
                }
                
                if(is_array($text)){
                    return $CommonService->doImg($msg,$text);
                }
                
                return $CommonService->doText($msg,$text);
        }
    }

}

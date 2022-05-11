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
            'ToUserName' => 'gh_e1096de195d0',
            'FromUserName' => 'o3bf-t7x7M8OYxDg1pzrOYz6Jzkg',
            'CreateTime' => '1648288388',
            'MsgType' => 'text',
            'Content' => '导入u3hrSelT#d5da6008569153a3f1ccce9b9a0decfc',
            'MsgId' => '23597816170740364',
            'Encrypt' => 'HCs2n37Bnj4tdJJWiJh/eVQI0aTo3zrjjci/1gAGa9z/kRFJwp7LiV36Jswy2EHTyHUSUk7As2Dz6VgMHNlLDbkaxdr1APdXGLtnufJYE7oHPEFp3/AKs3U4NpKMDjDsDTCs9QlKWlHpoYZ9caSI9Co+2JKKDI/RgncgL51B8qVVHb+KqLZbisNeu3iWQiyhudRyho68KkuIrlxhaPGJ+0Nbd1REhfdBqiR71I2XylUI9hwhztSTnLCD0QQ7T+OM9sfpJTFldwjLzpC0zZAvdxEMfzia8Jo/Yva8pcsvDY4XXs7UkjXhrdKD6k9AMAJjO7eLdiWg29XCorGhUDVLzFCp32JxFokht65RCXFCDD9h7diD1qm9pGwS5mqIQrsaudYL61BgWMrVVH+66XuayaXi/XoFI4Yb0LwZZzuacV6c3/OelmbMusSEXgUUoOgZtjLTTsbyxyFgRl8k6K1TXg==',
        ) ;
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

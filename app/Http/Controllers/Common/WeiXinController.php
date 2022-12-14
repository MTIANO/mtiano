<?php

namespace App\Http\Controllers\Common;


use App\Models\MtUser;
use App\Services\Api\OpenApiService;
use App\Services\Api\WeiXinService;
use App\Services\Common\CommonService;
use App\Services\Common\ImgService;
use App\Services\Common\YsService;
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

    public function openapi(){
        //$asd = (new OpenApiService())->models();
        $asd = (new OpenApiService())->completions('宇宙的秘密');
        $choices = $asd['choices'];
        $text = '';
        foreach ($choices as $value){
            $text .= $value['text'];
        }
        dump($text);die;
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
            'CreateTime' => '1655199131',
            'MsgType' => 'text',
            'Content' => '我是谁',
        );
        $user = (new MtUser())->getUserByWinXinId($msg['FromUserName']);
        $CommonService = new CommonService();
        dump($CommonService->manage($msg,$user));
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
        $user = (new MtUser())->getUserByWinXinId($msg['FromUserName']);
        if(!$user){
            $CommonService->doText($msg,'获取用户失败!');
        }


        //Log::channel('daily')->info($msg);
        switch ($msg['MsgType']){
            case'event':
                if($msg['Event'] === 'subscribe' ){
                    $text = '欢迎来到会员的世界!';
                    (new WeiXinService())->send('关注通知','新增一位关注者',date('Y-m-d H:i:s'));
                    return $CommonService->doText($msg,$text);
                }
                if($msg['Event'] === 'unsubscribe' ){
                    (new WeiXinService())->send('取消关注通知','失去一位关注者',date('Y-m-d H:i:s'));
                    $CommonService->disableUser($msg);
                    return true;
                }
                if($msg['Event'] === 'CLICK' ){
                    if($msg['EventKey'] === 'YS'){
                        return $CommonService->doText($msg,(new YsService($user))->get_user());
                    }
                }
            case'text':
                $open = (new OpenApiService())->completions($msg['Content']);
                $choices = $open['choices'];
                $text = '';
                foreach ($choices as $value){
                    $text .= $value['text'];
                }

                /*$text = $CommonService->manage($msg,$user);
                if($text === false){
                    $text = '指令无效,更多功能指令请联系本人!(目前开放:老黄历, 图片, bog)';
                }elseif($text === true){
                    $text = '操作成功!';
                }

                if(is_array($text)){
                    return $CommonService->doImg($msg,$text);
                }*/

                return $CommonService->doText($msg,$text);
        }
    }

}

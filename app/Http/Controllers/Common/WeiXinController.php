<?php

namespace App\Http\Controllers\Common;


use App\Jobs\OpenApiPush;
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

    public function test(){
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
                        return $CommonService->doText($msg,'遭受攻击，暂停服务');
                        //return $CommonService->doText($msg,(new YsService($user))->get_user());
                    }
                }
            case'text':
                OpenApiPush::dispatch(['user_info' => $msg,'text' => $msg['Content']]);
                //return $CommonService->doText($msg,'回答生成中，请稍等！');
                /*$text = $CommonService->manage($msg,$user);
                if($text === false){
                    $text = '指令无效,更多功能指令请联系本人!(目前开放:老黄历, 图片, bog)';
                }elseif($text === true){
                    $text = '操作成功!';
                }

                if(is_array($text)){
                    return $CommonService->doImg($msg,$text);
                }

                return $CommonService->doText($msg,$text);*/
        }
    }

}

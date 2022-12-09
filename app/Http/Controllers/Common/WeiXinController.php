<?php

namespace App\Http\Controllers\Common;


use App\Models\MtUser;
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
            'Content' => 'https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=4a53d8a25d19df7717af99fea4b46319ec6b57&timestamp=1653954959&lang=zh-cn&device_type=mobile&ext=%7b%22loc%22%3a%7b%22x%22%3a-3086.455322265625%2c%22y%22%3a252.09190368652345%2c%22z%22%3a-4422.65625%7d%2c%22platform%22%3a%22IOS%22%7d&game_version=CNRELiOS2.7.0_R8029328_S8227893_D8227893&plat_type=ios&region=cn_gf01&authkey=0XGfsLwEHXTbzzyKxRVETXoTzBcjzeLVr%2fyBkYZGJfcuKquK4gr5YpgDLcO1ATC4DTOngRkBLQ5GzfmfqY3%2fAcHh%2f0HjVCjoODqpTZNbVcPuXaCzvSI25TfDanhN6fmcJcOHLeSjnHqe%2f04iP1IHTzcGmZzte165k2XsyvsYltfPcxDqL3XyT2h3nAsFbuS49ufiaJNUPlDvziuOmTAAwvUQElYcCwcsD2syTFvl5l7%2bVasAcVRAaQZ6TUWxLD05jYiqhhFdvyLVezPkbq%2feosn1HqyNtxJoVBeK4FE0PuWQP%2f%2b%2babZpEpZFQ92xQKq3qE8ULD7Gwo0r2vADY2a3vwT3LvWNKn9pHfW6C7lO%2b3V951oiT%2b0t2NtS%2fdL5VjNkw4nnKreIfXBRln5qUnPvNksGKb%2f47HWhkNOFBUICSv3Ws2V%2fQ9j7wQC4IphubMGjaQhFOhbUlRfXcaX%2bgFIaVgUUyQ3gl2WMKJfsMASKoUB7Nq3m%2fj3xewWaNUpkZoPwXUqw%2fK%2f3WkCK5g1%2bezga1aN4RLjUoZZ7D2ne6s4HKl7z7GixEmg57dUf%2baa1Jkytt2Zr6Baeq2Mo7mir4aGd9X4tr43rSciw1ftf%2bzKqe2V04go3iCrZv80vAlsi6PSB0FhfTaG3qNlr5QtEjDypTUUcP9%2fBqpIpQRP8Nr3wF9ohqlA%2bPG6uuGht2Nurp6t9ZbTcoCY5%2f8Zpw23HC38p9Y1YGkFkWGNA9sKWVnnSK8G%2bT%2fdiNmypj5U5eG%2bCKnTOcnHOLz2r1SZv1443yathYn5u%2fxPZ454ql%2bx2m%2bmjeOcCACkDJ9MwxOjzPgsdpFOMdrAyKzu0cn2vykHi28p%2f5HUufwFiXSRMyesam1rEP5A5Q8Of6umZN7yC%2bp6kdOBgnv6Il4CC9ttHwfHcmUWh0Q8VoA%2f92JSyrtR2JgYLSwBpj7xSEuae3hLL4lFvxLlf6PKJGKsxUAKWaEcPeoQv6lp%2bQ1rf8hDdxW2gjamDlxsCb2aI9K5rr1OalcUqaUWs&game_biz=hk4e_cn',
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
        return $CommonService->doText($msg,'遭受恶意攻击，暂停服务!');
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
                $text = $CommonService->manage($msg,$user);
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

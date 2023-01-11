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



        dump(strlen('亲爱的，

我想说的是，我爱你。我爱你的一切，从你的笑容到你的眼睛，从你的声音到你的触感，从你的思想到你的行为，我都爱你。

我爱你，因为你是我最好的朋友，你总是在我最需要的时候出现，你总是给我最好的建议，你总是支持我，你总是陪我一起度过最难熬的时刻。

我爱你，因为你是我最亲密的伙伴，你总是陪我一起分享快乐，你总是陪我一起分担忧愁，你总是陪我一起度过最美好的时光。

我爱你，因为你是我最坚强的支柱，你总是在我最艰难的时刻给我力量，你总是在我最迷茫的时刻给我方向，你总是在我最孤独的时刻给我温暖。

我爱你，因为你是我最美丽的梦想，你总是在我最深的梦里出现，你总是在我最美的梦里出现，你总是在我最美好的梦里出现。

我爱你，因为你是我最真实的感受，你总是在我最深的心里出现，你总是在我最真实的心里出现，你总是在我最真实的心里出现。

我爱你，因为你是我最珍贵的礼物，你总是在我最珍贵的时刻出现，你总是在我最珍贵的地方出现，你总是在我最珍贵的心里出现。

我爱你，因为你是我最美好的回忆，你总是在我最美好的时光里出现，你总是在我最美好的地方出现，你总是在我最美好的心里出现。

我爱你，因为你是我最爱的人，你总是在我最爱的时刻出现，你总是在我最爱的地方出现，你总是在我最爱的心里出现。

亲爱的，我爱你，永远爱你，直到永远。

爱你的，
XXX'));die;
        /*dump((new WeiXinService())->custom_text('oERWv6qbxUaXC6Thly0ggeAkVilM','2c0geqIdBXLD1qjOERPKr9VEIOblffIi3EnLJC8bdn-wr3GMu02CXYd3yVK9WcBy','mpnews'));die;*/
        /*$data = [
            [
                'title' => '临时文案',
                'author' => 'czw',
                'content' => '图文消息的具体内容，支持 HTML 标签，必须少于2万字符，小于1M，且此处会去除 JS ,涉及图片 url 必须来源 "上传图文消息内的图片获取URL"接口获取。外部图片 url 将被过滤。',
                'thumb_media_id' => "2c0geqIdBXLD1qjOERPKr5TgdQbQTuIllAcyjdKddknAZ1YJocbNGlzFewYEwgcK",
            ]
        ];

        dump((new WeiXinService())->draft_add($data));die;*/
        /*$open = (new OpenApiService())->completions('说说对SQL语句优化有哪些方法？');
        $choices = $open['choices'];
        $text = '';
        foreach ($choices as $value){
            $text .= $value['text'];
        }
        dump($text);
        $lines = explode("\n\n", $text);
        array_shift($lines);
        $text = implode("\n\n", $lines);
        dump($text);die;*/
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

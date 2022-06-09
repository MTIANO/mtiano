<?php

namespace App\Http\Controllers\Common;


use App\Models\MtUser;
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
            'CreateTime' => '1654689638',
            'MsgType' => 'text',
            'Content' => 'UM_distinctid=181431c85cc8e9-0e93b75107fd01-6447264a-1fa400-181431c85cdcaa; _ga=GA1.2.779477119.1654688353; _gid=GA1.2.1169290039.1654688353; _MHYUUID=1cce2923-ee5a-47ae-84ca-3e78fb57dbb3; ltoken=jnLIQxLN7eSOsIybv5Kb2Dc7AqOLWfntlm8H4MZ3; ltuid=5606889; cookie_token=8hgxUBmHNw89JjwnsGvl43n3X93zQ8HKTNHugAGg; account_id=5606889; _gat=1; CNZZDATA1274689524=1438297071-1654685809-%7C1654685809; DEVICEFP_SEED_ID=8ce0c8211e10c60e; DEVICEFP_SEED_TIME=1654688745431; DEVICEFP=38d7ea80ec94e; login_uid=5606889; login_ticket=DqAbVkSFuEbgdHV3O8AvR4Wbl05OLam5Ynxxup82',
            'MsgId' => '23689460290934637',
            'Encrypt' => '6u3GWHxXjCiajLuefF01tF4fu+pJjF4nQHwB9ZLSWnjaIzvI8QyePEHlTTfEwT18dctB/BRHVxb21x+2340DGFcO+ZJpehfndxWTX7sYvpoQfihKOluzu5TbqHNJujgOCUlRsmz/8749XG2U2n2vM5uAPZWj8gqrBh2iMCbUjDf6UfuBxrzRc+lPFPi2BjJ2TXD/VRidWBIys6VAuHes5dTsaOre03WNPwhlMicsQTb8/Z7//muTI9DP+9+wHPF1bOREhioJ6aMmd2UIISVCxJYHv6okUq9TZvSIl0paAgicY03NYxAZoZP4n+IamJbLgLVTsW6Tunh/K8A7nA9xrHGA1wWbPI5C9kqo6wgKek8BO87t2wYrnwVWC8Pt569Cxw4fIfH+Z4T8gO+WM0du6whObfdENhDKR+7PmckQ5khDKAvrMq5K5ZCw9SsBZSVU+5FxiAgHqDK7Y6miXhzWRedwOPuiNpelgC0dTtKyeTXHE8aJHhk1XrOYfyQHgD9tMqe6ftBfL2gwLvE6Z9bvrl7+xyOhASZqYDViVfYqf9e6c6ziBVmiav5s1io+bpsG/Mchzf01gpBSSEEqbgKPKEognWPYhPqECGkwcJMXa6SbELw9emg5MijCipibEp27weaJEjWJSWaVswO4JDzvR28x0r8kgs7r8M8m3XSHnBDVHZLRsRIG7ive8DV5Vqe/zVYuP9WEiBWkXcFspoF4gB3vr/kHQat05yEdvdfSodMAZoo2Z6SF60PT9inTdVD38Tz9NuLRqiD2Y6GT81YBz+eXIBifLiPoOGPst8nHRN+awnWj0p7pA6QctX9GX2ushTbgllsz5kUvU/hJsfsHL9PNlceWZFX5e1v+HFGVR+i9pwiyogxxjXCs5XYo556ike858wcFRsBhIWvt2BqdXgb/mJ8nFcd+HdFQe4EdPqCfNeSZfUoK9km/jo5j8CI11cvbdaBDT+eAJiguIejAvTdz8EJ+f7JxK1eZD136G44lYfTSWaaaYG30xDoOE/A221Ld/1BA3HI6gRa7HZYqZr2oBsflajKlKpaRyczIRd5pQA3E9PDex0vPb9QaT0s8w+ISA7cP6u6sdxLjTTp5aSh8FprxQs7U9f2YjyJLip77euswbNK6Muv+sbdme9yqjp59XJmDQsJholbXIkJYMCUwtqkCAe0ihr/OhhTkn8s=',
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
                    return $CommonService->doText($msg,$text);
                }
                if($msg['Event'] === 'unsubscribe' ){
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

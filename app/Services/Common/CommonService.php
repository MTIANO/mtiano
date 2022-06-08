<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Services\Common;

use App\Models\MtBill;
use App\Models\MtBogMsg;
use App\Models\MtServiceBill;
use App\Models\MtUser;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Translation\t;

class CommonService
{
    
    private array $_msg_template = [
        'text' => '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>',//文本回复XML模板
        'image' => '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId><![CDATA[%s]]></MediaId></Image></xml>',//图片回复XML模板
        'music' => '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[music]]></MsgType><Music><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><MusicUrl><![CDATA[%s]]></MusicUrl><HQMusicUrl><![CDATA[%s]]></HQMusicUrl><ThumbMediaId><![CDATA[%s]]></ThumbMediaId></Music></xml>',//音乐模板
        'news' => '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>%s</ArticleCount><Articles>%s</Articles></xml>',// 新闻主体
        'news_item' => '<item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>',//某个新闻模板
    ];
    
    public static $OK = 0;
    public static $ValidateSignatureError = -40001;
    public static $ParseXmlError = -40002;
    public static $ComputeSignatureError = -40003;
    public static $IllegalAesKey = -40004;
    public static $ValidateAppidError = -40005;
    public static $EncryptAESError = -40006;
    public static $DecryptAESError = -40007;
    public static $IllegalBuffer = -40008;
    public static $EncodeBase64Error = -40009;
    public static $DecodeBase64Error = -40010;
    public static $GenReturnXmlError = -40011;
    
    public static $block_size = 32;
    
    
    //公众号检查
    public function checkSignature($get){
        if($get['signature'] && $get['timestamp'] && $get['nonce']){
            $signature = $_GET["signature"];
            $echostr = $_GET["echostr"] ?? '';
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];
            $token = 'R3Bi2TP720y3ZdF8BB0i3103P5PiR3TP';
            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );
            if( $tmpStr == $signature ){
                return response($echostr);
            }else{
                return false;
            }
        }
    }
    
    //获取内容
    public function getMsg(){
        $postxml = file_get_contents('php://input');
        $data = simplexml_load_string($postxml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_encode($data);
        $data = json_decode($data,true);
        return $data;
    }
    
    //拼接文本
    public function doText($msg,$text=''): string
    {
        $to = $msg['FromUserName'];
        $from = $msg['ToUserName'];
        return sprintf($this->_msg_template['text'], $to,$from, time(), $text);
    }
    
    //拼接图片
    public function doImg($msg,$text=''): string
    {
        $to = $msg['FromUserName'];
        $from = $msg['ToUserName'];
        return sprintf($this->_msg_template['image'], $to,$from, time(), $text['media_id']);
    }
    
    //获取token
    public function getAccessToken(){
        $key = 'weixin:accesstoken';
       // $access_token = Cache::get($key);
        //if(!$access_token){
            $appid = env('WEIXIN_APPID');
            $secret = env('WEIXIN_APPSECRET');
            $http = new \GuzzleHttp\Client;
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
            $rel = $http->get($url);
            $rel = json_decode($rel->getBody(),true);
            $access_token = $rel['access_token'];
            //Cache::add($key,$access_token,$rel['expires_in']);
            return $access_token;
        //}
        return $access_token;
    }
    
    //添加用户
    public function addUser($msg)
    {
        $user = (new MtUser())->getUserByWinXinId($msg['FromUserName']);
        Log::channel('daily')->info($user);
        if($user){
            $user = [
                'status' => 1,
            ];
            $rel = MtUser::where('winxin_id',$msg['FromUserName'])->update($user);
            if($rel){
                return true;
            }
    
            return false;
        }
        $user = [
            'winxin_id' => $msg['FromUserName'],
            'status' => 1,
        ];
        $rel = MtUser::create($user);
        if(!$rel){
            return false;
        }
    }
    
    
    //取消关注
    public function disableUser($msg){
        $user = (new MtUser())->getUserByWinXinId($msg['FromUserName']);
        if(!$user){
            return true;
        }
        $user = [
            'status' => 2,
        ];
        $rel = MtUser::where('winxin_id',$msg['FromUserName'])->update($user);
        if($rel){
            return true;
        }else{
            return false;
        }
    }
    
    public function isLinkBog($msg,$user){
        $is_link = MtBogMsg::query()->where('user_id', $user['id'],)->orderByDesc('created_at')->first();
        if(!$is_link){
            return false;
        }
        
        if($is_link['msg'] === 'bogend'){
            return false;
        }
        
        if((time() - strtotime($is_link['created_at'])) >= 600){
            return false;
        }
        return true;
    }
    
    //处理消息
    public function manage($msg): bool|string|array
    {
        $user = (new MtUser())->getUserByWinXinId($msg['FromUserName']);
        if(!$user){
            return false;
        }
    
        if($msg['Content'] === '原神'){
            return (new YsService())->get_user();
        }
    
        if($msg['Content'] === 'bogend'){
            return (new BogService())->bogEnd($msg,$user);
        }
        
        if($msg['Content'] === 'bog'){
            return (new BogService())->bogStart($msg,$user);
        }
    
        if($this->isLinkBog($msg,$user) === true){
            return (new BogService())->bog($msg,$user);
        }
    
        if($msg['Content'] === '上传图片'){
            return (new ImgService())->uploadImg($msg);
        }
    
        if($msg['Content'] === '图片'){
            return (new ImgService())->getRandImg($msg);
        }
    
        if($msg['Content'] === '老黄历'){
            return $this->lhl($user);
        }
        
        if($msg['Content'] === '账单'){
            return $this->todayBills($user);
        }
    
        if($msg['Content'] === '昨日账单'){
            return $this->yesterdayBills($user);
        }
    
        if(str_contains($msg['Content'], '发送给猪头')){
            return $this->sendMsgToZhu($user['id'],$msg);
        }
        
        $Content = explode('-',$msg['Content']);
        //记录账单
        if(count($Content) === 4){
            return $this->saveBills($user['id'],$Content);
        }
        return false;
    }
    
    public function lhl($user){
        $url = "http://v.juhe.cn/laohuangli/d";
        $data = [
            'key' => env('JH_LHL_KEY'),
            'date' => date('Y-m-d')
        ];
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['error_code'] != 0){
            return '操作失败';
        }else{
            $text = [];
            unset($rel['result']['id']);
            foreach ($rel['result'] as $key => $value){
                switch ($key){
                    case'yangli':
                        $text[] = '阳历：'.$value;
                        break;
                    case'yinli':
                        $text[] = '阴历：'.$value;
                        break;
                    case'wuxing':
                        $text[] = '五行：'.$value;
                        break;
                    case'chongsha':
                        $text[] = '冲煞：'.$value;
                        break;
                    case'baiji':
                        $text[] = '百忌：'.$value;
                        break;
                    case'jishen':
                        $text[] = '吉神：'.$value;
                        break;
                    case'yi':
                        $text[] = '宜：'.$value;
                        break;
                    case'xiongshen':
                        $text[] = '凶神：'.$value;
                        break;
                    case'ji':
                        $text[] = '忌：'.$value;
                        break;
                }
            }
            return implode('
',$text);
        }
    }
    
    
    //发送账单给肥猪
    public function sendMsgToZhu($user_id,$msg){
        $url = "https://tui.juhe.cn/api/plus/pushApi";
        $token = env('QIYEWEIXIN_TOKEN');
        $service_id = env('QIYEWEIXIN_ZHU_SERVICEID');
    
        $Content = explode('-',$msg['Content']);
        
        $bill_data = [
            'user_id' => $user_id,
            'date' => date('Ymd'),
            'money' => $Content[2],
            'source' => '猪猪',
            'remark' => $Content[1],
        ];
        $rel = MtServiceBill::create($bill_data);
        if(!$rel){
            return '记录失败';
        }
        
        $title = "账单推送";
        $content = '会员服务已结束，本次服务为'.$Content[1].'，服务金额为'.$Content[2].'元人民币，服务是否满意。请给五星好评并wx红包支付服务费。';
        $doc_type = "txt";
        $data = [
            'token' => $token,
            'service_id' => $service_id,
            'title' => $title,
            'content' => $content,
            'doc_type' => $doc_type
        ];
        
        $http = new \GuzzleHttp\Client;
        $rel = $http->post($url,['form_params' => $data]);
        $rel = json_decode((string)$rel->getBody(), true);
        if($rel['code'] === 200){
            return '发送成功';
        }else{
            return $rel['reason'];
        }
    }
    
    //昨日账单
    public function yesterdayBills($user): bool|string
    {
        $where = [
            'user_id' => $user['id'],
            'date' => date('Ymd', strtotime('-1 day'))
        ];
        $text = [];
        $bills = MtBill::query()
            ->where($where)
            ->get()->toArray();
        if(!$bills){
            return '暂无数据!';
        }
        $num_z = 0;
        $num_s = 0;
        foreach ($bills as $value){
            if($value['type'] === 1){
                $num_z += $value['money'];
                $type = '支出';
            }else{
                $num_s += $value['money'];
                $type = '收入';
            }
            $text[] = $value['source'].$type.$value['money'].'元,用于'.$value['remark'];
        }
        $text[] = '总支出:'.$num_z.'元';
        $text[] = '总收入:'.$num_s.'元';
        return implode('
',$text);
    }
    
    //今日账单
    public function todayBills($user): bool|string
    {
        $where = [
            'user_id' => $user['id'],
            'date' => date('Ymd H:i:s')
        ];
        $text = [];
        $bills = MtBill::query()
            ->where($where)
            ->get()->toArray();
        if(!$bills){
            return '暂无数据!';
        }
        $num_z = 0;
        $num_s = 0;
        foreach ($bills as $value){
            if($value['type'] === 1){
                $num_z += $value['money'];
                $type = '支出';
            }else{
                $num_s += $value['money'];
                $type = '收入';
            }
            $text[] = $value['source'].$type.$value['money'].'元,用于'.$value['remark'];
        }
        $text[] = '总支出:'.$num_z.'元';
        $text[] = '总收入:'.$num_s.'元';
        return implode('
',$text);
    }
    
    
    //保存账单
    public function saveBills($user_id,$bills){
        $bill_data = [
            'user_id' => $user_id,
            'date' => date('Ymd'),
            'money' => $bills[1],
            'source' => $bills[2],
            'remark' => $bills[3],
        ];
        if($bills[0] == '支出'){
            $bill_data['type'] = 1;
        }elseif($bills[0] == '收入'){
            $bill_data['type'] = 2;
        }
        $rel = MtBill::create($bill_data);
        if(!$rel){
            return false;
        }
    
        return true;
    }
    
    
    public function getUserInfo($openid){
        $http = new \GuzzleHttp\Client;
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->getAccessToken() . "&openid=" . $openid;
        $user = $http->get($url);
        return json_decode($user->getBody(),true);
    }
    
    
    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return array|string
     */
    public function decrypt($encrypted, $appid)
    {
        
        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->key, 0, 16);
            mcrypt_generic_init($module, $this->key, $iv);
    
            $decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
    
    
            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(self::$DecryptAESError, null);
        }
        
        
        try {
            //去除补位字符
            $result = $this->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            //print $e;
            return array(self::$IllegalBuffer, null);
        }
        if ($from_appid != $appid)
            return array(self::$ValidateAppidError, null);
        return array(0, $xml_content);
        
    }
    
    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    public function encode($text)
    {
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = self::$block_size - ($text_length % self::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::$block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }
    
    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return string
     */
    function decode($text)
    {
        
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
    
    function Sec2Time($time){
        if(is_numeric($time)){
            $value = array(
                 "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            /*if($time >= 31556926){
                $value["years"] = floor($time/31556926);
                $time = ($time%31556926);
            }*/
            if($time >= 86400){
                $value["days"] = floor($time/86400);
                $time = ($time%86400);
            }
            if($time >= 3600){
                $value["hours"] = floor($time/3600);
                $time = ($time%3600);
            }
            if($time >= 60){
                $value["minutes"] = floor($time/60);
                $time = ($time%60);
            }
            $value["seconds"] = floor($time);
            //return (array) $value;
            $t=$value["days"] ."天". $value["hours"] ."小时". $value["minutes"] ."分".$value["seconds"]."秒";
            Return $t;
            
        }else{
            return (bool) FALSE;
        }
    }

}

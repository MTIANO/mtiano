<?php

namespace App\Console\Commands;

use App\Jobs\WeiboPush;
use App\Models\MtYsCookie;
use App\Services\Api\WeiBoService;
use App\Services\Api\WeiXinService;
use App\Services\Api\YsService;
use App\Services\Common\MysService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        /*$log = file_get_contents('/tmp/抽卡记录.txt');
        $log = json_decode($log,true);*/
        
        $url = 'https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=4a53d8a25d19df7717af99fea4b46319ec6b57&timestamp=1653954959&lang=zh-cn&device_type=mobile&ext=%7b%22loc%22%3a%7b%22x%22%3a-3086.455322265625%2c%22y%22%3a252.09190368652345%2c%22z%22%3a-4422.65625%7d%2c%22platform%22%3a%22IOS%22%7d&game_version=CNRELiOS2.7.0_R8029328_S8227893_D8227893&plat_type=ios&region=cn_gf01&authkey=0XGfsLwEHXTbzzyKxRVETXoTzBcjzeLVr%2fyBkYZGJfcuKquK4gr5YpgDLcO1ATC4DTOngRkBLQ5GzfmfqY3%2fAcHh%2f0HjVCjoODqpTZNbVcPuXaCzvSI25TfDanhN6fmcJcOHLeSjnHqe%2f04iP1IHTzcGmZzte165k2XsyvsYltfPcxDqL3XyT2h3nAsFbuS49ufiaJNUPlDvziuOmTAAwvUQElYcCwcsD2syTFvl5l7%2bVasAcVRAaQZ6TUWxLD05jYiqhhFdvyLVezPkbq%2feosn1HqyNtxJoVBeK4FE0PuWQP%2f%2b%2babZpEpZFQ92xQKq3qE8ULD7Gwo0r2vADY2a3vwT3LvWNKn9pHfW6C7lO%2b3V951oiT%2b0t2NtS%2fdL5VjNkw4nnKreIfXBRln5qUnPvNksGKb%2f47HWhkNOFBUICSv3Ws2V%2fQ9j7wQC4IphubMGjaQhFOhbUlRfXcaX%2bgFIaVgUUyQ3gl2WMKJfsMASKoUB7Nq3m%2fj3xewWaNUpkZoPwXUqw%2fK%2f3WkCK5g1%2bezga1aN4RLjUoZZ7D2ne6s4HKl7z7GixEmg57dUf%2baa1Jkytt2Zr6Baeq2Mo7mir4aGd9X4tr43rSciw1ftf%2bzKqe2V04go3iCrZv80vAlsi6PSB0FhfTaG3qNlr5QtEjDypTUUcP9%2fBqpIpQRP8Nr3wF9ohqlA%2bPG6uuGht2Nurp6t9ZbTcoCY5%2f8Zpw23HC38p9Y1YGkFkWGNA9sKWVnnSK8G%2bT%2fdiNmypj5U5eG%2bCKnTOcnHOLz2r1SZv1443yathYn5u%2fxPZ454ql%2bx2m%2bmjeOcCACkDJ9MwxOjzPgsdpFOMdrAyKzu0cn2vykHi28p%2f5HUufwFiXSRMyesam1rEP5A5Q8Of6umZN7yC%2bp6kdOBgnv6Il4CC9ttHwfHcmUWh0Q8VoA%2f92JSyrtR2JgYLSwBpj7xSEuae3hLL4lFvxLlf6PKJGKsxUAKWaEcPeoQv6lp%2bQ1rf8hDdxW2gjamDlxsCb2aI9K5rr1OalcUqaUWs&game_biz=hk4e_cn';
        $page = 1;
        $gacha_type = 301;
        $end_id = 0;
        $query = parse_url($url)['query'];
        $query = explode('&',$query);
        $query_all = [];
        foreach ($query as &$value){
            $value_ = explode('=', $value);
            $query_all[$value_[0]] = $value_[1];
        }
        $query_all = array_merge($query_all,['size' => 20,'page' => $page,'gacha_type' => $gacha_type,'end_id' => $end_id]);
        $params = $this->url_encode($query_all);
        $list = (new YsService())->getGachaLog($params);
        if(!is_array($list)){
            return false;
        }
        $log_list = $list;
        dump('第1页完成');
        $n = 1;
        while ($list){
            //$query_all['page'] = $page++;
            $query_all['end_id'] = end($list)['id'];
            $params = $this->url_encode($query_all);
            $list = (new YsService())->getGachaLog($params);
            if(!is_array($list)){
                dump($list);
                $list = false;
            }else{
                $log_list = array_merge($log_list,$list);
            }
            $n++;
            dump('第'.$n.'页完成');
            sleep(1);
        }
        //dump(json_encode($log_list,JSON_UNESCAPED_UNICODE));
        
        $log = array_reverse($log_list);
        $goods = [];
        $goods_ssr = [];
        $num = 0;
        foreach ($log as $key => $log_value){
            $num++;
            if($log_value['rank_type'] == 5){
                $log_value['name'] = $log_value['name'].'_'.$key;
                $goods_ssr[$log_value['name']] = [
                    'count' => isset($goods_ssr[$log_value['name']]['count']) ? $goods_ssr[$log_value['name']]['count']+1 : 1,
                    'num' => $num,
                    'time' => $log_value['time'],
                    'rank_type' => $log_value['rank_type'],
                ];
                $num=0;
            }else{
                $goods[$log_value['name']] = [
                    'count' => isset($goods[$log_value['name']]['count']) ? $goods[$log_value['name']]['count']+1 : 1,
                    'num' => $num,
                    'time' => $log_value['time'],
                    'rank_type' => $log_value['rank_type'],
                ];
            }
        }
        $key = array_column(array_values($goods), 'rank_type');
        array_multisort($key, SORT_DESC, $goods);
        $txt = "已".$num."抽未出金 \r\n";
        $txt_ = [];
        foreach ($goods_ssr as $key => $value){
            $key = substr($key, 0, strrpos($key, "_"));
            $txt_[] =$key."(".$value['num'].")";
        }
        $txt .= implode(',',$txt_);
        dump($txt);
        //return $txt;
       // $user_info = (new WeiBoService())->get_user_info(7418196413);
       // WeiboPush::dispatch(['user_info' => $user_info,'f_value' => 7418196413]);
        /*$weibo_list = (new \App\Services\Api\WeiBoService())->get_mymblog($this->argument('user'));
        foreach ($weibo_list as $value){
            if($value['id'] === 4785366592651569){
                if(isset($value['pic_infos']) && $value['pic_infos']){
                    $original_pictures = [];
                    $original_pictures_live = [];
                    foreach ($value['pic_infos'] as $pic_value){
                        $original_pictures_live[] = $pic_value['video'];
                        $original_pictures[] = $pic_value['mw2000']['url'];
                    }
                }
            }
        }
        dump($original_pictures_live);
        dump($original_pictures);die;*/
        
        
        
        /*$user_list = MtYsCookie::query()->get()->toArray();
        foreach($user_list as $value){
            WeiboPush::dispatch($value['user_id']);
        }
        dump($user_list);die;*/
        //$job = WeiboPush::dispatch(11111);
        //dump($this->argument('user'));
    
        /*$live_value = 'https://video.weibo.com/media/play?livephoto=https%3A%2F%2Fus.sinaimg.cn%2F001C8A1Zgx07Xcu5m0cM0f0f0100nwkw0k01.mov';
        $user_info['screen_name'] = 'test';
        if($live_value){
            $live_value = urldecode($live_value);
            $live_file = file_get_contents($live_value);
            $live_name = explode('/',$live_value);
            $live_name = end($live_name);
            $live_name = explode('?',$live_name);
            Storage::disk('weibo')->put($user_info['screen_name'].'/'.$live_name[0], $live_file);
        }*/
        //dump($url);die;
        //$user_list = MtYsCookie::query()->where('user_id',$this->argument('user'))->first()->toArray();
        //(new MysService($this,$user_list['cookie'],$user_list['user_id']))->ys_sign($this->argument('user'));
        //dump((new \App\Services\Common\YsService(['id' => $this->argument('user')]))->get_user());
    }
    
    function url_encode($params){
        $tmp = [];
        foreach ($params as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        return implode('&', $tmp);
    }
}

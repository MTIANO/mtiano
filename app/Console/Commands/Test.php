<?php

namespace App\Console\Commands;

use App\Jobs\WeiboPush;
use App\Models\MtYsCookie;
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
    
        $live_value = 'https://video.weibo.com/media/play?livephoto=https%3A%2F%2Fus.sinaimg.cn%2F001C8A1Zgx07Xcu5m0cM0f0f0100nwkw0k01.mov';
        $user_info['screen_name'] = 'test';
        if($live_value){
            $live_value = urldecode($live_value);
            $live_file = file_get_contents($live_value);
            $live_name = explode('/',$live_value);
            $live_name = end($live_name);
            $live_name = explode('?',$live_name);
            Storage::disk('weibo')->put($user_info['screen_name'].'/'.$live_name[0], $live_file);
        }
        //dump($url);die;
        //$user_list = MtYsCookie::query()->where('user_id',$this->argument('user'))->first()->toArray();
        //(new MysService($this,$user_list['cookie'],$user_list['user_id']))->ys_sign($this->argument('user'));
        //dump((new \App\Services\Common\YsService(['id' => $this->argument('user')]))->get_user());
    }
}

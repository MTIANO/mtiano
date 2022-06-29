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
        /*$user_list = MtYsCookie::query()->get()->toArray();
        foreach($user_list as $value){
            WeiboPush::dispatch($value['user_id']);
        }
        dump($user_list);die;*/
        //$job = WeiboPush::dispatch(11111);
        //dump($this->argument('user'));
    
        /*$video_url = 'http://f.video.weibocdn.com/u0/lUzljx4ggx07XeeP6Bt601041200doMY0E010.mp4?label=mp4_720p&template=720x1560.24.0&ori=0&ps=1CwnkDw1GXwCQx&Expires=1656523192&ssig=MEuCidl%2FH%2B&KID=unistore,video';
        $user_info['screen_name'] = '我是狗头萝莉';
        if($video_url){
            $video_file = file_get_contents($video_url);
            $video_name = explode('/',$video_url);
            $video_name = end($video_name);
            $video_name = explode('?',$video_name);
            Storage::disk('weibo')->put($user_info['screen_name'].'/'.$video_name[0], $video_file);
        }*/
        //dump($url);die;
        //$user_list = MtYsCookie::query()->where('user_id',$this->argument('user'))->first()->toArray();
        //(new MysService($this,$user_list['cookie'],$user_list['user_id']))->ys_sign($this->argument('user'));
        //dump((new \App\Services\Common\YsService(['id' => $this->argument('user')]))->get_user());
    }
}

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
    
        $value_img = 'http://f.video.weibocdn.com/u0/A1VE75Nigx07Ugq9fUhW010412008x8d0E010.mp4?label=mp4_720p&template=720x1560.24.0&trans_finger=0dec003e4dad885964301ff5a1db7715&ori=0&ps=1CwnkDw1GXwCQx&Expires=1656345630&ssig=sOfJgYTkFr&KID=unistore,video';
        $file = file_get_contents($value_img);
        $name = explode('/',$value_img);
        $name = end($name);
        $name = explode('?',$name);
        Storage::disk('weibo')->put('test/'.$name[0], $file);
        //dump($url);die;
        //$user_list = MtYsCookie::query()->where('user_id',$this->argument('user'))->first()->toArray();
        //(new MysService($this,$user_list['cookie'],$user_list['user_id']))->ys_sign($this->argument('user'));
        //dump((new \App\Services\Common\YsService(['id' => $this->argument('user')]))->get_user());
    }
}

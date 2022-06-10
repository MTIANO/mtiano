<?php

namespace App\Console\Commands;

use App\Services\Api\WeiXinService;
use App\Services\Api\YsService;
use App\Services\Common\MysService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

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
        //(new WeiXinService())->menu_create();
        $url = 'http://f.video.weibocdn.com/u0/SEorMrIGgx07Wq0de6JW010412004hGL0E010.mp4?label=mp4_720p&template=720x960.24.0&ori=0&ps=1CwnkDw1GXwCQx&Expires=1654887258&ssig=LCH45w481G&KID=unistore,video';
        $file = file_get_contents($url);
        Storage::disk('weibo')->put('SEorMrIGgx07Wq0de6JW010412004hGL0E010.mp4', $file);
        die;
        $url = 'https://wx1.sinaimg.cn/mw2000/0065fV3lgy1gteg9hrrhhj617i2m84qq02.jpg,https://wx4.sinaimg.cn/mw2000/0065fV3lgy1gteg9mfu3lj617i2m84qq02.jpg,https://wx1.sinaimg.cn/mw2000/0065fV3lgy1gteg9jc4yfj617i2m8b2a02.jpg,https://wx4.sinaimg.cn/mw2000/0065fV3lgy1gteg9tl5itj617i2m87wi02.jpg,https://wx2.sinaimg.cn/mw2000/0065fV3lgy1gteg9pvdb1j617i2m81ky02.jpg,https://wx2.sinaimg.cn/mw2000/0065fV3lgy1gteg9ku56zj617i2m8e8202.jpg,https://wx2.sinaimg.cn/mw2000/0065fV3lgy1gteg9of6e2j617i2m87wi02.jpg,https://wx3.sinaimg.cn/mw2000/0065fV3lgy1gteg9fyf8yj617i2m84qq02.jpg,https://wx3.sinaimg.cn/mw2000/0065fV3lgy1gteg9rp7ggj61r03401ky02.jpg';
        $url = explode(',',$url);
        foreach ($url as $value){
            $file = file_get_contents($value);
            $name = explode('/',$value);
            Storage::disk('weibo')->put(end($name), $file);
        }
        dump($url);die;
    }
}

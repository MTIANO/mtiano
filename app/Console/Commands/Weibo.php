<?php

namespace App\Console\Commands;

use App\Jobs\WeiboPush;
use App\Models\MtUser;
use App\Models\MtWeiBo;
use App\Models\MtWeiBoUser;
use App\Services\Api\WeiBoService;
use App\Services\Api\WeiXinService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Weibo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:weibo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微博爬取';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('微博获取开始!');
        $follow = explode(',',env('WEIBO_FOLLOW'));
        foreach ($follow as $f_value){
            $user_info = (new WeiBoService())->get_user_info($f_value);
            if(!is_array($user_info)){
                $this->error($user_info);
                continue;
            }
            WeiboPush::dispatch(['user_info' => $user_info,'f_value' => $f_value]);
            //(new \App\Services\Common\WeiboService())->saveWeibo($user_info,$f_value);
            $this->info('获取'.$user_info['screen_name'].'的微博结束');
        }
        $this->info('微博获取完成!');
    }
    
    
}

<?php

namespace App\Console\Commands;

use App\Services\Api\WeiXinService;
use Illuminate\Console\Command;

class Send extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send';

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
        $this->info('获取用户列表!');
        $user_list = (new WeiXinService())->get_user();
        $first = '服务功能更新通知!';
        $keyword1 = "米游社原神自动签到/完成每日任务功能上线,\r\n需要完成cookie配置后即可在每天凌晨自动完成原神签到!";
        $keyword2 = date('Y-m-d H:i:s');
        $sand_url = 'https://mp.weixin.qq.com/s?__biz=MzkzNzM0MjczNw==&mid=2247483657&idx=1&sn=8e41d1e1bfd11718b442fac1ed5f598e&chksm=c291a377f5e62a61920cd67963c292b0d2e889d7d41c02f2718416bd56d4fcfeccfa0a0758a0#rd';
        $remark = '点击后下拉查看cookie配置方式';
        foreach ($user_list as $item){
            $this->info('给'.$item.'发送');
            $this->info((new WeiXinService())->send($first,$keyword1,$keyword2,$sand_url,$remark,$item));
            $this->info('给'.$item.'发送完成');
            sleep(1);
        }
        $this->info('发送完成!');
    }
}

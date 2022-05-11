<?php

namespace App\Console\Commands;

use App\Services\Common\BaiduService;
use App\Services\Common\WeiboService;
use Illuminate\Console\Command;

class Uploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baidu_pan:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '百度网盘推送';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->info((new BaiduService())->upload_all('/www/wwwroot/weibo/weibo/'));
    }
}

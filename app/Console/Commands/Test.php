<?php

namespace App\Console\Commands;

use App\Services\Api\WeiXinService;
use Illuminate\Console\Command;

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
        (new WeiXinService())->send('微博更新通知','XXXX更新了微博: XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX','www.baidu.com');
    }
}

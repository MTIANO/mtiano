<?php

namespace App\Console\Commands;

use App\Services\Api\WeiXinService;
use App\Services\Api\YsService;
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
        (new YsService())->get_user_info('cn_gf01','193403219');
    }
}

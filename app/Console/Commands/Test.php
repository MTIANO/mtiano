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
        /*$user_list = MtYsCookie::query()->get()->toArray();
        foreach($user_list as $value){
            WeiboPush::dispatch($value['user_id']);
        }
        dump($user_list);die;*/
        //$job = WeiboPush::dispatch(11111);
        //dump((new \App\Services\Common\YsService(['id' => 44]))->get_user());
    }
}

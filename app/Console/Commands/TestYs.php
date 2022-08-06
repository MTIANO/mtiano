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

class TestYs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testys {user}';

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
        //dump((new \App\Services\Common\YsService(['id' => $this->argument('user')]))->get_user());
        $user_list = MtYsCookie::query()->where('user_id',15)->first()->toArray();
        (new MysService($this,$user_list['cookie'],$user_list['user_id']))->AuthSign($user_list['user_id']);
    }
}

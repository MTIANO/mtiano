<?php

namespace App\Console\Commands;

use App\Models\MtYsCookie;
use App\Services\Common\MysService;
use Illuminate\Console\Command;

class Mys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mys:sign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '米游社自动签到';
    
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('米游社自动签到开始!');
        $this->info('获取签到用户列表!');
        $user_list = MtYsCookie::query()->get()->toArray();
        if(!$user_list){
            $this->info('无签到用户!');
        }
        
        foreach($user_list as $value){
            $this->info('开始签到用户'.$value['user_id']);
            (new MysService($this,$value['cookie'],$value['user_id']))->AuthSign($value['user_id']);
            $this->info('结束签到用户'.$value['user_id']);
        }
        $this->info(('米游社自动签到完成'));
    }
}

<?php
/**
 * @author  czw
 * @title
 * @date    2022/3/30 11:10 上午
 */

namespace App\Console\Commands;

use App\Models\MtUser;
use App\Models\MtYsCookie;
use App\Services\Common\MysService;
use App\Services\Common\WeiboService;
use App\Services\Common\YsService;
use Illuminate\Console\Command;

class Push extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weibo:push';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微博推送';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $this->info('米游社自动提醒开始!');
        $this->info('获取用户列表!');
        $user_list = MtYsCookie::query()->get()->toArray();
        if(!$user_list){
            $this->info('无用户!');
        }
    
        foreach($user_list as $value){
            $this->info('开始用户'.$value['user_id']);
            $this->info((new YsService(['id' => $value['user_id']]))->remind(MtUser::query()->where('id',$value['user_id'])->value('winxin_id')));
            $this->info('结束用户'.$value['user_id']);
        }
        $this->info(('米游社自动提醒完成'));
    }
    

}

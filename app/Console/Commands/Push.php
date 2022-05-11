<?php
/**
 * @author  czw
 * @title
 * @date    2022/3/30 11:10 上午
 */

namespace App\Console\Commands;

use App\Services\Common\WeiboService;
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
        $this->info((new WeiboService())->push());
    }
    

}

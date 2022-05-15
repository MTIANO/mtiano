<?php

namespace App\Console\Commands;

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
        (new MysService($this))->AuthSign();
        $this->info(('米游社自动签到完成'));
    }
}

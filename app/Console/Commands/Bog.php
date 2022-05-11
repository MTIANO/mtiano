<?php

namespace App\Console\Commands;

use App\Models\MtBogCookie;
use App\Services\Common\BaiduService;
use App\Services\Common\BogService;
use App\Services\Common\WeiboService;
use Illuminate\Console\Command;

class Bog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bog:sign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bog自动签到';

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
        $cookie = MtBogCookie::query()->where('status',1)->get()->toArray();
        foreach ($cookie as $item) {
            $user['id'] = $item['user_id'];
            $rel = (new BogService())->sign($item['id'],$user);
            dump($rel);
            sleep(1);
        }
    }
}

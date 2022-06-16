<?php

namespace App\Jobs;

use App\Services\Api\WeiXinService;
use App\Services\Common\WeiboService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WeiboPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $cond;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cond = [])
    {
        $this->cond = $cond;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new WeiboService())->saveWeibo($this->cond['user_info'],$this->cond['f_value']);
    }
}

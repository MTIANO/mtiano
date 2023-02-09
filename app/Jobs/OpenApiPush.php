<?php

namespace App\Jobs;

use App\Services\Api\OpenApiService;
use App\Services\Api\WeiXinService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class OpenApiPush implements ShouldQueue
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
        try {
            try {
                $open = (new OpenApiService())->completions($this->cond['text']);
                $choices = $open['choices'];
                $text = '';
                foreach ($choices as $value){
                    $text .= $value['text'];
                }
            }catch (Exception $e) {
                $text = $e->getMessage();
            }

            if(strlen($text) >= 1200){
                $data = [
                    [
                        'title' => $this->cond['text'].'-回复',
                        'author' => 'czw',
                        'content' => $text,
                        'thumb_media_id' => "2c0geqIdBXLD1qjOERPKr5TgdQbQTuIllAcyjdKddknAZ1YJocbNGlzFewYEwgcK",
                    ]
                ];
                $media_id = (new WeiXinService())->draft_add($data);
                (new WeiXinService())->custom_text($this->cond['user_info']['FromUserName'],$media_id,'mpnews');
            }else{
                (new WeiXinService())->custom_text($this->cond['user_info']['FromUserName'],$text);
            }
        }catch (Exception $e) {
            dump($e->getMessage());
        }
    }
}

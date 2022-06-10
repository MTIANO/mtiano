<?php

namespace App\Console\Commands;

use App\Models\MtUser;
use App\Models\MtWeiBo;
use App\Models\MtWeiBoUser;
use App\Services\Api\WeiBoService;
use App\Services\Api\WeiXinService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Weibo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:weibo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微博爬取';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('微博获取开始!');
        $follow = explode(',',env('WEIBO_FOLLOW'));
        foreach ($follow as $f_value){
            $user_info = (new WeiBoService())->get_user_info($f_value);
            if(!is_array($user_info)){
                $this->error($user_info);
                continue;
            }
            $this->info('正在更新'.$user_info['screen_name'].'的用户信息');
            $this->saveUser($user_info);
            $this->info('正在获取'.$user_info['screen_name'].'的微博');
            $weibo_list = (new WeiBoService())->get_mymblog($f_value);
            if(!is_array($weibo_list)){
                $this->error($weibo_list);
                continue;
            }
            
            foreach ($weibo_list as $value){
                $weibo = MtWeiBo::query()->where('id',$value['id'])->first();
                $original_pictures = [];
                $video_url = $value['page_info']['media_info']['mp4_720p_mp4'] ?? '';
                if(isset($value['pic_infos']) && $value['pic_infos']){
                    foreach ($value['pic_infos'] as $pic_value){
                        $original_pictures[] = $pic_value['mw2000']['url'];
                    }
                }
                $data = [
                    'id' => $value['id'],
                    'user_id' => $value['user']['id'],
                    'content' => $value['text_raw'],
                    'original_pictures' => implode(',',$original_pictures),
                    'original' => 1,
                    'video_url' => $video_url,
                    'publish_place' => $value['region_name']??'',
                    'publish_time' => date('Y-m-d H:i:s',strtotime($value['created_at'])),
                    'publish_tool' => $value['source'],
                    'up_num' => $value['attitudes_count'],
                    'retweet_num' => $value['reposts_count'],
                    'comment_num' => $value['comments_count'],
                ];
                if($weibo){
                    MtWeiBo::query()->where('id',$value['id'])->update($data);
                }else{
                    MtWeiBo::query()->create($data);
                    sleep(1);
                    $first = $user_info['screen_name'].'更新了微博';
                    $weibo_url = 'https://weibo.com/'.$data['user_id'].'/'.$value['mblogid'];
                    (new WeiXinService())->send($first,$data['content'],$data['publish_time'],$weibo_url);
                    foreach ($original_pictures as $value_img){
                        $file = file_get_contents($value_img);
                        $name = explode('/',$value_img);
                        Storage::disk('weibo')->put($user_info['screen_name'].'/'.end($name), $file);
                    }
                }
            }
            $this->info('获取'.$user_info['screen_name'].'的微博结束');
        }
        $this->info('微博获取完成!');
    }
    
    public function saveUser($user_info){
        $user = MtWeiBoUser::query()->where('id',$user_info['id'])->first();
        $data = [
            'id' => $user_info['id'],
            'nickname' => $user_info['screen_name'],
            'gender' => $user_info['gender'],
            'location' => $user_info['location'],
            'description' => $user_info['description'],
            'verified_reason' => $user_info['verified_reason']??'',
            'weibo_num' => $user_info['statuses_count'],
            'following' => $user_info['friends_count'],
            'followers' => $user_info['followers_count'],
            'work' => '',
            'education' => '',
            'talent' => '',
            'birthday' => '',
        ];
        if(!$user){
            MtWeiBoUser::query()->create($data);
        }else{
            MtWeiBoUser::query()->where('id',$user_info['id'])->update($data);
        }
    }
}

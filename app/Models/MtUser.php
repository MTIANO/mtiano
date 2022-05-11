<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class MtUser extends Base
{
    use HasFactory, Notifiable;
    
    //protected $table = 'user';
    //const TABLE_NAME = 'user';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    protected $dateFormat = 'U';
    
    
    public function getUserByWinXinId($wixin_id){
        return self::query()->where('winxin_id',$wixin_id)->first();
    }
}

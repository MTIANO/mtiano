<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Weibo extends Model
{
    use HasFactory, Notifiable;
    
    protected $table = 'weibo';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    protected $dateFormat = 'U';
    
    protected $primaryKey = '';
    
    protected $connection = 'weibo_mysql';
    
}

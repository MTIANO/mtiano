<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class MtBill extends Base
{
    use HasFactory, Notifiable;
    
    //protected $table = 'user';
    //const TABLE_NAME = 'user';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    protected $dateFormat = 'U';
    
}

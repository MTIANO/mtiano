<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MtAuth extends Base
{
    use HasFactory, Notifiable;
    
    protected $table = 'mt_auth';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    protected $dateFormat = 'U';
}

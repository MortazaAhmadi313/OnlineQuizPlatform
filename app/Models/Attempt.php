<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    //
    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'status',
        'started_at',
        'completed_at',
    ];
}

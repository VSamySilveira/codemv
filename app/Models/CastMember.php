<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Traits\Uuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    //protected $table = 'cast_members';
    protected $fillable = ['name', 'type'];
    protected $dates = ["deleted_at"];
    
    public $incrementing = false;
}

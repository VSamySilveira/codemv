<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Traits\Uuid;
    protected $table = 'cast_members';
    protected $fillable = ['name', 'type', 'is_active'];
    protected $dates = ["deleted_at"];
    protected $casts = [
        'id' => 'string',
        'type' => 'smallInteger',
        'is_active' => 'boolean'
    ];
    public $incrementing = false;
}

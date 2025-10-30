<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUserActivity;

class Block extends Model
{
    use SoftDeletes;
    use TracksUserActivity;

    protected $fillable = [
        'block_group_id',
        'key',
        'name',
        'order',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(BlockGroup::class, 'block_group_id');
    }
}

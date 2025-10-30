<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUserActivity;

class BlockGroup extends Model
{
    use TracksUserActivity;

    protected $fillable = [
        'key',
        'name',
        'order',
        'created_by',
        'updated_by',
    ];

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $table = 'content_blocks';

    protected $fillable = [
        'content_id',
        'block_id',
        'order',
        'active',
        'settings',
    ];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }
}

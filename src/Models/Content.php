<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUserActivity;

class Content extends Model
{
    use SoftDeletes;
    use TracksUserActivity;

    protected $fillable = [
        'content_type_id',
        'title',
        'slug',
        'order',
        'active',
        'settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
    ];

    public function type()
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }

    public function blocks()
    {
        return $this->hasMany(ContentBlock::class);
    }

    public function media()
    {
        return $this->morphMany(Relation::class, 'source')
            ->where('target_type', 'media');
    }

    public function related()
    {
        return $this->morphMany(Relation::class, 'source');
    }
}

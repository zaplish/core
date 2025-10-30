<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'relation_type',
        'field_name',
        'order',
    ];

    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
}

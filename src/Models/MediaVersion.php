<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\MediaHelper;
use Illuminate\Support\Facades\Log;

class MediaVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'size_key',
        'uri',
        'path',
        'extension',
        'width',
        'height'
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    protected static function booted()
    {
        static::deleting(function ($media) {
            try {
                MediaHelper::deleteFile($media);
            } catch (\Throwable $e) {
                Log::warning("File for media {$media->id} could not be deleted: " . $e->getMessage());
            }
        });
    }
}

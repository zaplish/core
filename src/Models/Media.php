<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TracksUserActivity;
use App\Helpers\SlugHelper;
use App\Helpers\MediaHelper;

class Media extends Model
{
    use TracksUserActivity;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'alt_text',
        'uri',
        'path',
        'extension',
        'mime_type',
        'media_type',
        'filename_original',
        'size',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function versions()
    {
        return $this->hasMany(MediaVersion::class);
    }

    protected static function booted()
    {
        static::saving(function ($media) {
            $media->slug = !empty($media->title) ? SlugHelper::getSlug($media->title) : null;
        });

        static::deleting(function ($media) {
            MediaHelper::deleteFile($media);

            foreach ($media->versions as $version) {
                $version->delete();
            }
        });
    }
}

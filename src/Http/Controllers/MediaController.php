<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Helpers\MediaHelper;

class MediaController extends Controller
{
    private static $imageMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/svg',
        'image/svg+xml',
        'image/gif',
        'image/avif',
        'image/x-icon',
        'image/ico',
        'image/vnd.microsoft.icon',
    ];

    /**
     * Show original media file
     */
    public function show(string $uuid, ?string $size = null)
    {
        $media = Media::where('uuid', $uuid)->first();

        if (!$media) {
            return self::fileNotFound();
        }

        if ($size) {
            $sizeKey = ltrim($size, '-');
            $version = $media->versions()->where('size_key', $sizeKey)->first();
            if (!$version) {
                return self::fileNotFound($media);
            }
            $path = $version->path;
        } else {
            $path = $media->path;
        }

        if (!Storage::disk('public')->exists($path)) {
            return self::fileNotFound($media);
        }

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'public, max-age=31536000',
            'Content-Disposition' => 'inline; filename="' . $media->slug . '.' . $media->extension . '"',
        ]);
    }

    public function fileNotFound($media = null)
    {
        if ($media && in_array($media->mime_type, self::$imageMimeTypes)) {
            return response(
                file_get_contents(public_path('img/image-placeholder.svg')),
                404,
                [
                    'Content-Type' => 'image/svg+xml',
                    'Cache-Control' => 'public, max-age=31536000',
                ]
            );
        }
        abort(404, 'File not found.');
    }
}

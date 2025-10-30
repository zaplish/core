<?php

namespace App\Helpers;

use App\Models\Media;
use App\Models\MediaVersion;
use Illuminate\Http\UploadedFile;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Exceptions\DecoderException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\SettingsHelper;

class MediaHelper
{
    /**
     * Store uploaded file in the media library
     */
    public static function store(UploadedFile $file, $replaceId = false): array
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $error = $file->getError();

            if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                $maxUpload = self::convertToBytes(ini_get('upload_max_filesize'));
                $maxPost   = self::convertToBytes(ini_get('post_max_size'));
                $limit     = min($maxUpload, $maxPost);

                return [
                    'success' => false,
                    'message' => __('admin::media.upload.error.errorFileExceedsMaxSize', [
                        'size' => round($limit / 1024 / 1024, 2) . ' MB',
                    ]),
                ];
            }

            return [
                'success' => false,
                'message' => $file->getErrorMessage(),
            ];
        }

        // Replace existing or create new media record
        if ($replaceId) {
            $media = Media::with('versions')->find($replaceId);

            if (!$media) {
                return [
                    'success' => false,
                    // TODO test
                    'message' => __('admin::media.upload.error.errorReplaceFileNotFound'),
                ];
            }

            // TODO delete old file
        } else {
            $media = new Media();
        }

        $documentMimes = [
            // PDF
            'pdf'  => 'application/pdf',

            // Microsoft Word
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',

            // Microsoft Excel
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',

            // Microsoft PowerPoint
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',

            // Microsoft Access
            'mdb'  => 'application/vnd.ms-access',

            // OpenDocument
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp'  => 'application/vnd.oasis.opendocument.presentation',
            'odg'  => 'application/vnd.oasis.opendocument.graphics',
            'odc'  => 'application/vnd.oasis.opendocument.chart',
            'odf'  => 'application/vnd.oasis.opendocument.formula',
            'odb'  => 'application/vnd.oasis.opendocument.database',
            'odi'  => 'application/vnd.oasis.opendocument.image',
            'odm'  => 'application/vnd.oasis.opendocument.text-master',
            'ott'  => 'application/vnd.oasis.opendocument.text-template',

            // Apple iWork
            'pages'   => 'application/vnd.apple.pages',
            'numbers' => 'application/vnd.apple.numbers',
            'key'     => 'application/vnd.apple.keynote',

            // Generic text
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'application/csv',
        ];

        // Decide media type from mime
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        if (str_starts_with($mimeType, 'image/')) {
            $mediaType = 'image';
        } else if (str_starts_with($mimeType, 'video/')) {
            $mediaType = 'video';
        } else if (str_starts_with($mimeType, 'audio/')) {
            $mediaType = 'audio';
        } else if (isset($documentMimes[$extension]) && in_array($mimeType, $documentMimes, true)) {
            $mediaType = 'document';
        } else {
            $mediaType = 'file';
        }

        // Save file depending on type
        if ($mediaType === 'image' && self::supportsMime($mimeType)) {
            try {
                $image = MediaHelper::read($file->getRealPath());
            } catch (DecoderException $e) {
                Log::warning("Failed to decode image {$file->getClientOriginalName()}: {$e->getMessage()}");

                return [
                    'success' => false,
                    'message' => __('admin::media.upload.error.' . (extension_loaded('imagick') ? 'errorFileDecode' : 'errorFileDecodeImagickNotInstalled')),
                ];
            }

            $saved = MediaHelper::saveFile(
                $file
            );

            $saved['width'] = $image->width();
            $saved['height'] = $image->height();
        } else {
            $saved = MediaHelper::saveFile(
                $file
            );
        }

        // Generate file hash
        $hash = md5_file($file->getRealPath());

        $meta = [
            'hash' => $hash,
        ];

        if (!empty($saved['width']) && !empty($saved['height'])) {
            $meta['width'] = $saved['width'];
            $meta['height'] = $saved['height'];
        }

        // Fill DB fields
        $fillData = [
            'uuid'              => $saved['uuid'],
            'path'              => $saved['path'],
            'uri'               => self::getMediaUri($saved['uuid']),
            'extension'         => $saved['extension'],
            'media_type'        => $mediaType,
            'filename_original' => $file->getClientOriginalName(),
            'mime_type'         => $saved['mime'],
            'size'              => $saved['size'],
            'meta'              => $meta,
        ];

        if (!$replaceId) {
            $fillData['title'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $media->fill($fillData);
        $media->save();

        // Generate versions if needed
        if (
            $mimeType === 'application/pdf' ||
            ($mediaType === 'image' && self::supportsMime($mimeType))
        ) {
            self::generateVersions($media);
        }

        $media->load('versions');

        return [
            'success' => true,
            'media'   => $media,
        ];
    }

    /**
     * Generate versions
     */
    public static function generateVersions(Media $media): void
    {
        // Skip unsupported cases
        if ($media->mime_type === 'application/pdf' && !class_exists(\Imagick::class)) {
            Log::warning("Skipping PDF preview for media {$media->id}: Imagick not installed");
            return;
        }

        if ($media->mime_type !== 'application/pdf' && !self::supportsMime($media->mime_type)) {
            Log::warning("Skipping generation of versions for media {$media->id}: Unsupported MIME type {$media->mime_type}");
            return;
        }

        $versions = self::getImageVersions();
        $folder = self::getMediaFolder($media->uuid);
        $convertToWebp = self::getConvertToWebp();
        $imageQuality = self::getImageQuality();

        foreach ($versions as $version) {
            $sizeKey = $version['id'];
            $maxWidth = $version['width'];
            $maxHeight = $version['height'];

            try {
                // Delete old version if exists
                if ($existing = $media->versions()->where('size_key', $sizeKey)->first()) {
                    self::deleteVersion($existing);
                }

                $saved = null;

                if ($media->mime_type === 'application/pdf') {
                    // PDF → use Imagick to generate first-page preview
                    $imagick = new \Imagick();
                    $imagick->setResolution(150, 150);
                    $imagick->readImage(storage_path("app/public/{$media->path}[0]"));
                    $imagick->setImageFormat('webp');
                    $imagick->setImageCompressionQuality(80);
                    $imagick->setImageBackgroundColor('white');
                    $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    $imagick->thumbnailImage($maxWidth, $maxHeight, true);

                    $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_preview_') . '.webp';
                    $imagick->writeImage($tmpPath);

                    $image = MediaHelper::read($tmpPath);
                    $saved = MediaHelper::saveImage(
                        $image,
                        folder: $folder,
                        maxWidth: $maxWidth,
                        maxHeight: $maxHeight,
                        imageQuality: 80,
                        convertToWebp: true,
                        stripAnimation: false,
                        uuid: $media->uuid,
                        sizeKey: $sizeKey,
                    );

                    @unlink($tmpPath);
                    $imagick->clear();
                    $imagick->destroy();
                } else {
                    // Regular image
                    $image = MediaHelper::read(storage_path("app/public/{$media->path}"));

                    $saved = MediaHelper::saveImage(
                        $image,
                        folder: $folder,
                        maxWidth: $maxWidth,
                        maxHeight: $maxHeight,
                        imageQuality: ($sizeKey === 'preview' ? min(80, $imageQuality) : $imageQuality),
                        convertToWebp: $convertToWebp,
                        stripAnimation: ($sizeKey === 'preview'),
                        uuid: $media->uuid,
                        sizeKey: $sizeKey,
                        crop: ($sizeKey === 'preview'),
                    );
                }

                if ($saved) {
                    MediaVersion::create([
                        'media_id'  => $media->id,
                        'size_key'  => $sizeKey,
                        'uri'       => self::getMediaUri($media->uuid, $sizeKey),
                        'path'      => $saved['path'],
                        'extension' => $saved['extension'],
                        'width'     => $saved['width'],
                        'height'    => $saved['height'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed generating version {$sizeKey} for media {$media->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Read an image from a file path or binary string
     */
    public static function read(string $pathOrData): Image
    {
        return self::getManager()->read($pathOrData);
    }

    /**
     * Save a file to storage
     */
    public static function saveFile(
        UploadedFile|string $file
    ): array {
        if ($file instanceof UploadedFile) {
            $extension = strtolower($file->getClientOriginalExtension());
            $mime      = $file->getMimeType();
            $size      = $file->getSize();
        } else {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mime      = mime_content_type($file);
            $size      = filesize($file);
        }

        $uuid   = Str::uuid()->toString();
        $folder = trim(self::getMediaFolder($uuid), '/');
        $path   = $folder . '/original.' . $extension;

        if ($file instanceof UploadedFile) {
            Storage::disk('public')->putFileAs(dirname($path), $file, basename($path));
        } else {
            Storage::disk('public')->put($path, file_get_contents($file));
        }

        return [
            'uuid'      => $uuid,
            'folder'    => $folder,
            'uri'       => self::getMediaUri($uuid),
            'path'      => $path,
            'extension' => $extension,
            'mime'      => $mime,
            'size'      => $size,
        ];
    }

    /**
     * Save an image to storage
     *
     * @param Image    $image
     * @param string   $path           Absolute path where to save the file
     * @param bool     $convertToWebp  Convert to WebP before saving
     * @param bool     $stripAnimation Take only first frame if animated
     * @param int|null $maxWidth       Optional max width
     * @param int|null $maxHeight      Optional max height
     * @param int      $quality        Quality
     *
     * @return array [path, extension, width, height]
     */
    public static function saveImage(
        Image $image,
        ?string $folder = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        ?int $imageQuality = null,
        bool $convertToWebp = true,
        bool $stripAnimation = false,
        ?string $uuid = null,
        ?string $sizeKey = null,
        bool $crop = false,
    ): array {
        if ($stripAnimation && extension_loaded('imagick')) {
            $core = $image->core()->native();
            if ($core instanceof \Imagick && $core->getNumberImages() > 1) {
                $core = $core->coalesceImages();
                $core->setIteratorIndex(0);
                $frame = $core->getImage();
                $image = self::getManager()->read($frame->getImageBlob());
            }
        }

        if ($maxWidth || $maxHeight) {
            if ($crop) {
                $image->cover($maxWidth ?? $maxHeight, $maxHeight ?? $maxWidth, 'center');
            } else {
                $image->scaleDown($maxWidth ?? null, $maxHeight ?? null);
            }
        }

        $mime = $image->encode()->mediaType();

        if ($convertToWebp) {
            if (($mime === 'image/png' || $mime === 'image/gif') && extension_loaded('imagick')) {
                $core = $image->core()->native();
                if ($core instanceof \Imagick) {
                    if ($core->getNumberImages() > 1) {
                        // Skip animated PNGs of GIFs
                    } else {
                        $core->setImageFormat('webp');
                        $core->setOption('webp:lossless', 'true');
                        $encoded = $core->getImageBlob();
                    }
                }
            }

            if (empty($encoded)) {
                $encoded = $image->encode(new WebpEncoder(quality: $imageQuality));
            }

            $mime = 'image/webp';
            $extension = 'webp';
        } else {
            switch ($mime) {
                case 'image/jpeg':
                    $encoded = $image->encode(new JpegEncoder(quality: $imageQuality));
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $encoded = $image->encode(new PngEncoder());
                    $extension = 'png';
                    break;
                case 'image/webp':
                    $encoded = $image->encode(new WebpEncoder(quality: $imageQuality));
                    $extension = 'webp';
                    break;
                default:
                    $encoded = $image->encode();
                    $extension = self::mimeToExtension($mime) ?? 'jpg';
            }
        }

        $uuid = $uuid ?? Str::uuid()->toString();
        if (!$folder) {
            $folder = self::getMediaFolder($uuid);
        }
        $folder = trim($folder, '/');
        $path   = $folder . '/' . ($sizeKey ? $sizeKey : 'original') . '.' . $extension;

        Storage::disk('public')->put($path, (string) $encoded);

        return [
            'uuid'      => $uuid,
            'folder'    => $folder,
            'uri'       => self::getMediaUri($uuid, $sizeKey),
            'path'      => $path,
            'extension' => $extension,
            'mime'      => $mime,
            'size'      => Storage::disk('public')->size($path),
            'width'     => $image->width(),
            'height'    => $image->height(),
        ];
    }

    /**
     * Get the media folder
     */
    private static function getMediaFolder($uuid): string
    {
        $folderStructure = self::getFolderStructure();

        $parts = [];

        // Media folder
        $parts[] = 'media';

        if ($folderStructure === 'date') {
            $parts[] = date('Y');
            $parts[] = date('m');
        }

        if ($folderStructure === 'hash') {
            $parts[] = substr($uuid, 0, 2);
            $parts[] = substr($uuid, 2, 2);
        }

        $parts[] = $uuid;

        return implode('/', $parts);
    }

    /**
     * Get the manager (Imagick preferred, GD fallback)
     */
    private static function getManager(): ImageManager
    {
        return extension_loaded('imagick')
            ? new ImageManager(new ImagickDriver())
            : new ImageManager(new GdDriver());
    }

    /**
     * Get media uri
     */
    public static function getMediaUri($uuid, ?string $sizeKey = null): string
    {
        return '/media/' . $uuid . ($sizeKey ? '/' . $sizeKey : '');
    }

    /**
     * Delete versions
     */
    public static function deleteVersions(Media $media): void
    {
        $versions = $media->versions;

        foreach ($versions as $version) {
            self::deleteVersion($version);
        }
    }

    /**
     * Delete version
     */
    public static function deleteVersion(MediaVersion $version): void
    {
        self::deleteFile($version);
        $version->delete();
    }

    /**
     * Delete media file
     */
    public static function deleteFile($media): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($media->path)) {
            $disk->delete($media->path);
            $folder = dirname($media->path);
            $files = $disk->allFiles($folder);
            if (empty($files)) {
                $disk->deleteDirectory($folder);
            }
        } else {
            Log::info("Skipping deleting missing file: {$media->path}");
        }
    }

    /**
     * Convert to bytes
     */
    private static function convertToBytes($val)
    {
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        $num = (int) $val;

        switch ($last) {
            case 'g':
                $num *= 1024;
            case 'm':
                $num *= 1024;
            case 'k':
                $num *= 1024;
        }

        return $num;
    }

    /**
     * Get supported MIME types for the current driver
     */
    private static function getSupportedImageMimes(): array
    {
        $manager = self::getManager();

        if ($manager->driver() instanceof ImagickDriver) {
            $imagick = new \Imagick();
            $formats = array_map('strtolower', $imagick->queryFormats());

            $extToMime = [
                'jpeg' => 'image/jpeg',
                'jpg'  => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'bmp'  => 'image/bmp',
                'tiff' => 'image/tiff',
                'heic' => 'image/heic',
                'heif' => 'image/heif',
                'avif' => 'image/avif',
                // 'svg'  => 'image/svg+xml',
                // 'ico'  => 'image/vnd.microsoft.icon',
            ];

            return array_values(array_intersect_key($extToMime, array_flip($formats)));
        }

        if ($manager->driver() instanceof GdDriver) {
            // GD is very limited
            return [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/bmp',
            ];
        }

        return [];
    }

    /**
     * Check if a MIME type is supported by current setup
     */
    public static function supportsMime(string $mime): bool
    {
        return in_array($mime, self::getSupportedImageMimes(), true);
    }

    /**
     * Mime type to extension
     */
    public static function mimeToExtension(string $mime): string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/bmp'  => 'bmp',
            'image/tiff' => 'tiff',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'image/avif' => 'avif',
            'image/x-tga' => 'tga',
            'image/svg+xml' => 'svg',
            'image/vnd.microsoft.icon' => 'ico',
            'image/x-icon' => 'ico',
            'image/x-win-bitmap' => 'bmp',
            'image/x-portable-bitmap' => 'bmp',
        ];

        return $mimeToExt[$mime] ?? null;
    }

    /**
     * Get folder structure
     */
    private static function getFolderStructure(): string
    {
        switch (SettingsHelper::get('media.folder-structure')) {
            case 'none':
                return 'none';
            case 'date':
                return 'date';
            case 'hash':
                return 'hash';
        }

        return 'date';
    }

    /**
     * Get image versions
     */
    private static function getImageVersions(): array
    {
        $versions = json_decode(SettingsHelper::get('media.image-versions'), true);

        $versions[] = [
            'id' => 'preview',
            'width' => 400,
            'height' => 400,
        ];

        return $versions;
    }

    /**
     * Get convert to WebP
     */
    private static function getConvertToWebp(): bool
    {
        return SettingsHelper::get('media.convert-to-webp') === '1';
    }

    /**
     * Get WebP quality
     */
    private static function getImageQuality(): int
    {
        return (int) SettingsHelper::get('media.image-quality');
    }
}

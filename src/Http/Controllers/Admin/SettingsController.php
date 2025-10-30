<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\SettingsHelper;

class SettingsController extends Controller
{
    public function siteInfo()
    {
        return view('admin::pages.settings.site-info');
    }

    public function siteVariables()
    {
        return view('admin::pages.settings.site-variables');
    }

    public function developer(?string $tab = null)
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $requiredPHP = $composer['require']['php'] ?? null;

        $path = storage_path();
        $free = disk_free_space($path);
        $total = disk_total_space($path);

        if ($tab === 'phpinfo') {
            phpinfo();
            return;
        }

        $serverInfo = [
            // Laravel
            'laravelVersion' => app()->version(),
            'laravelEnv' => config('app.env'),
            'laravelDebug' => config('app.debug') ? 1 : 0,
            // PHP
            'phpVersion' => PHP_VERSION,
            'phpVersionSuggested' => $this->normalizePhpRequirement($requiredPHP),
            'memoryLimit' => ini_get('memory_limit'),
            'memoryLimitSuggested' => '512M',
            'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            'uploadMaxFilesizeSuggested' => '32M',
            'postMaxSize' => ini_get('post_max_size'),
            'postMaxSizeSuggested' => '64M',
            'maxExecutionTime' => ini_get('max_execution_time'),
            'maxExecutionTimeSuggested' => '30',
            // Extensions
            'gd' => extension_loaded('gd') ? 1 : 0,
            'imagick' => extension_loaded('imagick') ? 1 : 0,
            // Disk
            'diskFree' => $free  !== false ? round($free / 1024 / 1024 / 1024, 2) . ' GB' : __('admin::settings.developer.notAvailable'),
            'diskTotal' => $total !== false ? round($total / 1024 / 1024 / 1024, 2) . ' GB' : __('admin::settings.developer.notAvailable'),
            // Timezone
            // TODO add ip2location timezones
            'timezone' => config('app.timezone'),
        ];

        $mediaSettings = [
            'folderStructure' => SettingsHelper::get('media.folder-structure'),
            'imageVersions' => SettingsHelper::get('media.image-versions'),
            'convertToWebp' => SettingsHelper::get('media.convert-to-webp'),
            'imageQuality' => SettingsHelper::get('media.image-quality'),
        ];

        return view('admin::pages.settings.developer', compact('serverInfo', 'mediaSettings', 'tab'));
    }

    /**
     * Normalize the PHP requirement
     * @param string $constraint
     * @return string|null
     */
    private function normalizePhpRequirement(string $constraint): ?string
    {
        // Handle multiple constraints like "^8.2|^9.0"
        $parts = preg_split('/\s*\|\s*/', $constraint);

        if (!$parts) {
            return null;
        }

        // Take the lowest acceptable version (first part)
        $first = $parts[0];

        // Strip non-numeric chars and ensure .0 suffix
        if (preg_match('/(\d+\.\d+)/', $first, $matches)) {
            return $matches[1] . '.0';
        }

        return null;
    }
}

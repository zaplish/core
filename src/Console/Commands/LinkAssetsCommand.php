<?php

namespace Zaplish\Core\Console\Commands;

use Illuminate\Console\Command;

class LinkAssetsCommand extends Command
{
    protected $signature = 'zaplish:assets:link 
                            {--force : Overwrite existing links if they already exist}';

    protected $description = 'Create or refresh symlinks for Zaplish admin and active theme assets.';

    public function handle(): int
    {
        $this->info('Linking Zaplish assets...');

        // Core admin assets
        $coreSource = base_path('vendor/zaplish/core/public/admin');
        $coreTarget = public_path('vendor/zaplish/admin');
        $this->link($coreSource, $coreTarget, 'Admin assets');

        // Active theme assets
        $theme = config('cms.theme');
        if (!file_exists(base_path("themes/{$theme}/public"))) {
            $this->warn("Theme '{$theme}' not found. Skipping theme assets.");
            return Command::SUCCESS;
        }
        $themeSource = base_path("themes/{$theme}/public");
        $themeTarget = public_path("themes/{$theme}");
        $this->link($themeSource, $themeTarget, "Theme '{$theme}' assets");

        $this->newLine();
        $this->info('Zaplish assets linked successfully.');
        return Command::SUCCESS;
    }

    protected function link(string $src, string $dest, string $label): void
    {
        if (!is_dir($src)) {
            $this->warn("{$label} not found: {$src}");
            return;
        }

        if (is_link($dest) || file_exists($dest)) {
            if ($this->option('force')) {
                $this->line("Replacing existing {$label} link...");
                @unlink($dest);
            } else {
                $this->line("{$label} already linked. Use --force to refresh.");
                return;
            }
        }

        @mkdir(dirname($dest), 0755, true);
        symlink($src, $dest);
        $this->line("{$label} linked → {$dest}");
    }
}

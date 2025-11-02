<?php

namespace Zaplish\Core\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class LinkAssetsCommand extends Command
{
    protected $signature = 'zaplish:assets:link
                            {--build : Build assets if missing before linking}
                            {--force : Overwrite existing links if they already exist}';

    protected $description = 'Create or refresh symlinks for Zaplish admin and active theme assets.';

    public function handle(): int
    {
        if ($this->option('build')) {
            $this->info('Building Zaplish assets...');
            $process = new Process(['php', 'artisan', 'zaplish:assets:build']);
            $process->setTimeout(5 * 60);
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if (!$process->isSuccessful()) {
                $this->error('Asset build failed. Cannot link.');
                return self::FAILURE;
            }
        } else {
            $this->error('Admin assets missing. Run "php artisan zaplish:assets:build" first.');
            return self::FAILURE;
        }

        $this->info('Linking Zaplish assets...');

        // Core admin assets
        $coreSource = base_path('vendor/zaplish/core/public/admin');
        $coreTarget = public_path('vendor/zaplish/admin');
        $this->link($coreSource, $coreTarget, 'Admin assets');

        // Active theme assets
        $theme = config('zaplish.theme');
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

        // TODO: Check for Symlink, maybe copy or have option
        symlink($src, $dest);
        $this->line("{$label} linked → {$dest}");
    }
}

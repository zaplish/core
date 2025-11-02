<?php
namespace Zaplish\Core\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Zaplish\Core\Helpers\SettingsHelper;

class BuildAssetsCommand extends Command
{
    protected $signature = 'zaplish:assets:build
                            {--dev : Run vite in development mode}';

    protected $description = 'Build Zaplish Core admin and theme assets via Vite';

    public function handle(): int
    {
        $mode = $this->option('dev') ? 'development' : 'production';

        // Build admin assets
        $this->info('Installing Zaplish admin npm dependencies...');
        $corePath = base_path('vendor/zaplish/core');

        if (!$this->runNpmInstall($corePath)) {
            return self::FAILURE;
        }

        $this->info("Building Zaplish admin assets in {$mode} mode...");
        if (!$this->runNpmBuild($corePath, $mode)) {
            return self::FAILURE;
        }

        // Build Theme
        $theme = SettingsHelper::get('zaplish.theme', 'zaplish');
        $themePath = base_path("themes/{$theme}");

        if (is_dir($themePath)) {
            $this->info("Building theme {$theme} assets...");
            if (!$this->runNpmInstall($themePath)) {
                return self::FAILURE;
            }

            if (!$this->runNpmBuild($themePath, $mode)) {
                return self::FAILURE;
            }
        } else {
            $this->warn("Theme folder not found: {$themePath}");
        }

        $this->info('All Zaplish assets built successfully.');
        return self::SUCCESS;
    }

    protected function runNpmInstall(string $path): bool
    {
        $process = new Process(['npm', 'install', '--progress=false', '--no-fund'], $path);
        $process->setTimeout(5 * 60);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            $this->error("npm install failed in {$path}");
            return false;
        }
        return true;
    }

    protected function runNpmBuild(string $path, string $mode): bool
    {
        $command = ['npm', 'run', $mode === 'production' ? 'build' : 'dev'];
        $process = new Process($command, $path);
        $process->setTimeout(5 * 60);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
        if (!$process->isSuccessful()) {
            $this->error("Vite build failed in {$path}");
            return false;
        }
        return true;
    }
}

<div class="tab-content__container">
    <div class="settings__wrapper">
        <div class="settings__container">
            <div class="settings__title">{{ __('admin::settings.developer.system.sections.laravel.title') }}</div>
            <div class="settings__items">
                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.laravel.items.laravelVersion'),
                    'value' => $serverInfo['laravelVersion'],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.laravel.items.laravelEnv'),
                    'value' => $serverInfo['laravelEnv'],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.laravel.items.laravelDebug'),
                    'value' => view('admin::pages.settings.partials.yes-no', [
                        'value' => $serverInfo['laravelDebug'],
                    ])->render(),
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'laravel-debug',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.laravel.warnings.laravelDebug'),
                        'show' => $serverInfo['laravelDebug'] && $serverInfo['laravelEnv'] === 'production',
                    ],
                ])
            </div>
        </div>
        <div class="settings__container">
            <div class="settings__title -has-link">
                <span>{{ __('admin::settings.developer.system.sections.php.title') }}</span>
                <a href="/admin/settings/developer/phpinfo" target="_blank" class="-small-text"><span class="icon -small-text-icon">open_in_new</span> {{ __('admin::settings.developer.system.sections.php.phpinfo') }}</a>
            </div>
            <div class="settings__items">
                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.phpVersion'),
                    'value' => $serverInfo['phpVersion'],
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'php-version',
                        'type' => 'error',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.phpVersion', ['value' => $serverInfo['phpVersionSuggested']]),
                        'show' => version_compare($serverInfo['phpVersion'], $serverInfo['phpVersionSuggested'], '<'),
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.memoryLimit'),
                    'value' => $serverInfo['memoryLimit'],
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'memory-limit',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.memoryLimit', ['value' => $serverInfo['memoryLimitSuggested']]),
                        'show' => $serverInfo['memoryLimit'] !== '-1' && (int) rtrim($serverInfo['memoryLimit'], 'KMGT') < (int) rtrim($serverInfo['memoryLimitSuggested'], 'KMGT'),
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.uploadMaxFilesize'),
                    'value' => $serverInfo['uploadMaxFilesize'],
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'upload-max-filesize',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.uploadMaxFilesize', ['value' => $serverInfo['uploadMaxFilesizeSuggested']]),
                        'show' => (int) rtrim($serverInfo['uploadMaxFilesize'], 'KMGT') < (int) rtrim($serverInfo['uploadMaxFilesizeSuggested'], 'KMGT'),
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.postMaxSize'),
                    'value' => $serverInfo['postMaxSize'],
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'post-max-size',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.postMaxSize', ['value' => $serverInfo['postMaxSizeSuggested']]),
                        'show' => (int) rtrim($serverInfo['postMaxSize'], 'KMGT') < (int) rtrim($serverInfo['postMaxSizeSuggested'], 'KMGT'),
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.maxExecutionTime'),
                    'value' => $serverInfo['maxExecutionTime'],
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'max-execution-time',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.maxExecutionTime', ['value' => $serverInfo['maxExecutionTimeSuggested']]),
                        'show' => (int) $serverInfo['maxExecutionTime'] < (int) $serverInfo['maxExecutionTimeSuggested'],
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.gd'),
                    'value' => view('admin::pages.settings.partials.yes-no', [
                            'value' => $serverInfo['gd'],
                        ])->render(),
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'gd',
                        'type' => 'error',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.gd'),
                        'show' => $serverInfo['gd'] === 0 && $serverInfo['imagick'] === 0,
                    ],
                ])

                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.php.items.imagick'),
                    'value' => view('admin::pages.settings.partials.yes-no', [
                        'value' => $serverInfo['imagick'],
                    ])->render(),
                    'warning' => [
                        'id' => 'ignore-system-warnings',
                        'key' => 'imagick',
                        'type' => 'warning',
                        'message' => __('admin::settings.developer.system.sections.php.warnings.imagick'),
                        'show' => $serverInfo['imagick'] === 0,
                    ],
                ])

            </div>
        </div>
        <div class="settings__container">
            <div class="settings__title">
                <span>{{ __('admin::settings.developer.system.sections.disk.title') }}</span>
            </div>
            <div class="settings__items">
                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.disk.items.diskFree'),
                    'value' => $serverInfo['diskFree'],
                ])
                
                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.disk.items.diskTotal'),
                    'value' => $serverInfo['diskTotal'],
                ])
            </div>
        </div>
        <div class="settings__container">
            <div class="settings__title">
                <span>{{ __('admin::settings.developer.system.sections.time.title') }}</span>
            </div>
            <div class="settings__items">
                @include('admin::pages.settings.partials.settings-item', [
                    'label' => __('admin::settings.developer.system.sections.time.items.timezone'),
                    'value' => $serverInfo['timezone'],
                ])
            </div>
        </div>
    </div>

    @if (!empty(Auth::user()->settings['ignore-system-warnings']))
    <div class="settings__links-container">
        <div class="settings__links">
            <span class="settings__link link" data-reset-warnings="ignore-system-warnings">{{ __('admin::settings.resetIgnoreWarnings') }}</span>
        </div>
    </div>
    @endif
</div>
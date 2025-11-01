<div class="settings__item">
    <div class="settings__label">{!! $label !!}</div>
    @if (isset($sublabel))
        <div class="settings__sublabel">{!! $sublabel !!}</div>
    @endif
    <div class="settings__value monospace">{!! $value !!}</div>
    @if (!empty($warning['show']))
        @if (empty(Auth::user()->settings['ignore-system-warnings'][$warning['key']]))
            <div class="settings__warning-container" data-warning="{{ $warning['key'] }}">
                <div class="settings__warning -{{ $warning['type'] ?? 'error' }}">
                    <span class="icon -small-text-icon">warning</span> {!! $warning['message'] !!}
                    <span class="link" data-warning-id="{{ $warning['id'] }}" data-remove-warning="{{ $warning['key'] }}">{{ __('admin::settings.ignoreWarning') }}</span>
                </div>
            </div>
        @endif
    @endif
</div>
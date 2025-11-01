<header class="header__wrapper">
    <div class="header__container">
        <div class="header__content">
            <div class="header__page-title-container">
                <div class="header__page-title h1">
                    @if (!empty($contentTitle))
                        {{ $contentTitle }}
                    @elseif (!empty($listData['config']['title']))
                        {{ __($listData['config']['title']) }}
                    @elseif (!empty($formData['config']['title' . (!empty($formData['item']) ? 'Edit' : 'New')]))
                        {{ __($formData['config']['title' . (!empty($formData['item']) ? 'Edit' : 'New')]) }}
                    @endif
                </div>
            </div>
            <div class="header__form-buttons">
                @if (!empty($listData))
                    <{!! !empty($listData['config']['editUriNew']) ? 'a href="' . $listData['config']['editUriNew'] . '"' : 'button' !!}
                        class="header__form-button button -medium -has-icon"
                        @if (!empty($listData['config']['addButtonAttributes']))
                            {!! join(' ', array_map(function($key, $value) {
                                return $key . '="' . $value . '"';
                            }, array_keys($listData['config']['addButtonAttributes']), $listData['config']['addButtonAttributes'])) !!}
                        @endif
                    ><span class="icon">add</span>{{ __('admin::list.buttons.add') }}</{{ !empty($listData['config']['editUriNew']) ? 'a' : 'button' }}>
                @elseif (!empty($formData))
                    @if (!empty($formData['config']['listRoute']))
                        <a href="{{ $formData['config']['listUri'] }}" class="header__form-button -icon"><div class="icon">arrow_back_ios_new</div></a>
                    @endif
                    <button
                        class="header__form-button button -medium"
                        data-save-form="{{ $formData['config']['key'] }}"
                    >{{ __('admin::form.buttons.save') }}</button>
                @endif
            </div>
        </div>
        <div class="header__user-menu-container">
            <div class="header__user-menu-toggler no-select{{ request()->routeIs('admin.profile.edit') ? ' -active' : '' }}" data-toggle-menu="user">
                <div class="header__user-menu-icon icon">account_circle</div>
            </div>

            <div data-menu="user" class="header__user-menu menu-overlay__wrapper">
                <div class="menu-overlay__links">
                    <a href="{{ route('admin.profile.edit') }}" class="menu-overlay__link{{ request()->routeIs('admin.profile.edit') ? ' -active' : '' }}">
                        <div class="menu-overlay__icon icon">account_circle</div>
                        <div class="menu-overlay__label">
                            {{ __('admin::users.profile.navTitle') }}
                        </div>
                    </a>
                    <a href="{{ route('admin.logout') }}" class="menu-overlay__link">
                        <div class="menu-overlay__icon icon">logout</div>
                        <div class="menu-overlay__label">
                            {{ __('admin::auth.logout.navTitle') }}
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

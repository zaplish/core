<div class="panel__wrapper">
    <div class="panel__header"></div>
    <div class="panel__container">
        <div class="panel__content">

            {{-- Dashboard --}}

            <nav class="panel__nav">
                <div class="panel__link-container{{ request()->routeIs('admin.dashboard.*') ? ' -active' : '' }}">
                    <a href="{{ route('admin.dashboard.view') }}" class="panel__link{{ request()->routeIs('admin.dashboard.*') ? ' -active' : '' }}">
                        <div class="panel__icon icon">dashboard</div>
                        <div class="panel__link-text">
                            {{ __('admin::panel.nav.dashboard.main') }}
                        </div>
                    </a>
                </div>
            </nav>

            {{-- Content --}}

            <div class="panel__label">
                {{ __('admin::panel.label.content') }}
            </div>
            <nav class="panel__nav">
                @foreach ($contentTypes as $type)
                    <div class="panel__link-container{{ request()->routeIs('admin.content.*') && request()->route('type') == $type->key ? ' -active' : '' }}">
                        <a href="{{ route('admin.content.list', ['type' => $type->key]) }}" class="panel__link{{ request()->routeIs('admin.content.*') && request()->route('type') == $type->key ? ' -active' : '' }}">
                            <div class="panel__icon icon">{{ $type->settings['icon'] ?? 'dangerous' }}</div>
                            <div class="panel__link-text">
                                {{ \Illuminate\Support\Facades\Lang::has('admin::content.' . $type->key . '.navTitle') ? __('admin::content.' . $type->key . '.navTitle') : $type->name }}
                            </div>
                        </a>
                    </div>
                @endforeach

                {{-- Media --}}

                <div class="panel__link-container{{ request()->routeIs('admin.media.*') ? ' -active' : '' }}">
                    <a href="{{ route('admin.media.list') }}" class="panel__link{{ request()->routeIs('admin.media.*') ? ' -active' : '' }}">
                        <div class="panel__icon icon">filter</div>
                        <div class="panel__link-text">
                            {{ __('admin::panel.nav.media.main') }}
                        </div>
                    </a>
                </div>
            </nav>

            {{-- Administration --}}

            @if (auth()->user()?->hasRole('admin') || auth()->user()?->hasRole('developer'))
                <div class="panel__label">
                    {{ __('admin::panel.label.administration') }}
                </div>
                <nav class="panel__nav">

                    {{-- Settings --}}

                    <div class="panel__link-container -has-subs{{ request()->routeIs('admin.settings.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.settings.site-info') }}" class="panel__link">
                            <div class="panel__icon icon">tune</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.settings.main') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.settings.site-info') }}" class="panel__link -sub{{ request()->routeIs('admin.settings.site-info') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.settings.siteInfo') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.settings.site-variables') }}" class="panel__link -sub{{ request()->routeIs('admin.settings.site-variables') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.settings.siteVariables') }}
                            </div>
                        </a>
                        @if (auth()->user()?->hasRole('developer'))
                            <a href="{{ route('admin.settings.developer', ['tab' => 'system']) }}" class="panel__link -sub{{ request()->routeIs('admin.settings.developer') ? ' -active' : '' }}">
                                <div class="panel__link-text">
                                    {{ __('admin::panel.nav.settings.developer') }}
                                </div>
                            </a>
                        @endif
                    </div>

                    {{-- Users --}}

                    <div class="panel__link-container{{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.profile.edit') ? ' -active' : '' }}">
                        <a href="{{ route('admin.users.list') }}" class="panel__link{{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.profile.edit') ? ' -active' : '' }}">
                            <div class="panel__icon icon">group</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.users.main') }}
                            </div>
                        </a>
                    </div>
                </nav>
            @endif

            {{-- Developer --}}

            @if (auth()->user()?->hasRole('developer'))
                <div class="panel__label">
                    {{ __('admin::panel.label.developer') }}
                </div>
                <nav class="panel__nav">

                    {{-- Themes --}}

                    <div class="panel__link-container -has-subs{{ request()->routeIs('admin.themes.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.themes.select') }}" class="panel__link">
                            <div class="panel__icon icon">palette</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.themes.main') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.themes.select') }}" class="panel__link -sub{{ request()->routeIs('admin.themes.select') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.themes.selectTheme') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.themes.variables') }}" class="panel__link -sub{{ request()->routeIs('admin.themes.variables') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.themes.themeVariables') }}
                            </div>
                        </a>
                    </div>

                    {{-- Menus --}}

                    <div class="panel__link-container -has-subs{{ request()->routeIs('admin.menus.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.menus.list') }}" class="panel__link{{ request()->routeIs('admin.menus.*') ? ' -active' : '' }}">
                            <div class="panel__icon icon">menu_open</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.menus.main') }}
                            </div>
                        </a>
                    </div>

                    {{-- Forms --}}

                    <div class="panel__link-container -has-subs{{ request()->routeIs('admin.forms.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.forms.list') }}" class="panel__link">
                            <div class="panel__icon icon">table_edit</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.forms.main') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.forms.list') }}" class="panel__link -sub{{ request()->routeIs('admin.forms.list') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.forms.list') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.forms.submissions') }}" class="panel__link -sub{{ request()->routeIs('admin.forms.submissions') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.forms.submissions') }}
                            </div>
                        </a>
                    </div>

                    {{-- Content types --}}

                    <div class="panel__link-container{{ request()->routeIs('admin.content-types.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.content-types.list') }}" class="panel__link{{ request()->routeIs('admin.content-types.*') ? ' -active' : '' }}">
                            <div class="panel__icon icon">list_alt</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.contentTypes.main') }}
                            </div>
                        </a>
                    </div>

                    {{-- Blocks --}}

                    <div class="panel__link-container -has-subs{{ request()->routeIs('admin.blocks.*') ? ' -active' : '' }}">
                        <a href="{{ route('admin.blocks.list') }}" class="panel__link">
                            <div class="panel__icon icon">widgets</div>
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.blocks.main') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.blocks.list') }}" class="panel__link -sub{{ request()->routeIs('admin.blocks.list') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.blocks.list') }}
                            </div>
                        </a>
                        <a href="{{ route('admin.blocks.groups') }}" class="panel__link -sub{{ request()->routeIs('admin.blocks.groups') ? ' -active' : '' }}">
                            <div class="panel__link-text">
                                {{ __('admin::panel.nav.blocks.groups') }}
                            </div>
                        </a>
                    </div>
                </nav>
            @endif
        </div>
    </div>
</div>

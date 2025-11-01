@if ($value === 1)
    <span class="icon -small-text-icon -has-margin">check_circle</span>{{ __('admin::app.yes') }}
@else
    <span class="icon -small-text-icon -has-margin">cancel</span>{{ __('admin::app.no') }}
@endif
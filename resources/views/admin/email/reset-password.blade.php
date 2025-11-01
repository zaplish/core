@include('admin::email.email-header')

<div style="font-size: 23px; font-weight: bold; margin: 0 0 24px; text-align: center; color: #fff">
    {!! __('admin::mail.resetPassword.title', ['name' => $mailData['data']['user']['name']]) !!}
</div>

<div style="margin: 0 0 32px; text-align: center">
    {!! __('admin::mail.resetPassword.textAboveButton') !!}
</div>

<div style="margin: 0 0 32px; text-align: center">
    <a
        class="laracms-mail-button"
        style="color: #fff; text-decoration: none"
        href="{{ $mailData['data']['buttonLink'] }}"
    >
        {{ __('admin::mail.resetPassword.buttonText') }}
    </a>
</div>

<div style="text-align: center">
    {!! __('admin::mail.resetPassword.textBelowButton') !!}
</div>

@include('admin::email.email-footer', [
    'footerText' => __('admin::mail.resetPassword.footer', ['app-name' => config('cms.name')])
])

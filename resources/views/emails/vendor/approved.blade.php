@component('mail::message')
# Congratulations {{ $vendor?->user?->name ?? 'User' }} 🎉

Your vendor account **{{ $vendor->business_name ?? '' }}** has been approved.

You can now log in and start managing your services.

@component('mail::button', ['url' => config('app.url') . '/vendor/login'])
Go to Vendor Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

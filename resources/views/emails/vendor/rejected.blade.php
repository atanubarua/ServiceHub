@component('mail::message')
# Vendor Application Update

Hello {{ $vendor?->user?->name ?? 'User' }},

Unfortunately, your vendor application for **{{ $vendor->business_name ?? '' }}** has been rejected.

If you believe this was a mistake, you may reapply.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

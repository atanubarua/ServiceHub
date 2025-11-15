<?php

namespace App\Listeners;

use App\Events\VendorStatusUpdatedEvent;
use App\Mail\VendorApprovedMail;
use App\Mail\VendorRejectedMail;
use App\Models\Vendor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendVendorStatusEmailListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VendorStatusUpdatedEvent $event): void
    {
        $vendor = $event->vendor;
        $user = $vendor->user;

        if (empty($user->email)) {
            logger('VENDOR_HAS_NO_EMAIL', ['vendor' => $vendor, 'user' => $user]);
        }

        if ($vendor->status == Vendor::STATUS_APPROVED) {
            Mail::to($user->email)->queue(new VendorApprovedMail($vendor));
        }

        if ($vendor->status == Vendor::STATUS_REJECTED) {
            Mail::to($user->email)->queue(new VendorRejectedMail($vendor));
        }
    }
}

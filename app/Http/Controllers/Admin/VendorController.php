<?php

namespace App\Http\Controllers\Admin;

use App\Events\VendorStatusUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index() {
        $vendors = Vendor::with('user')->paginate(10);
        return response()->json($vendors);
    }

    public function show($id) {
        $vendor = Vendor::find($id);

        if (empty($vendor)) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        return response()->json($vendor);
    }

    public function destroy($id) {
        $vendor = Vendor::find($id);

        if (empty($vendor)) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->delete();
        return response()->json(['message' => 'Vendor soft deleted']);
    }

    public function approve($id) {
        $vendor = Vendor::find($id);

        if (empty($vendor)) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if ($vendor->status == Vendor::STATUS_APPROVED) {
            return response()->json(['message' => 'Vendor is already approved']);
        }

        try {
            $vendor->update(['status' => Vendor::STATUS_APPROVED]);
            event(new VendorStatusUpdatedEvent($vendor));
            return response()->json(['message' => 'Vendor approved successfully']);
        } catch (\Throwable $th) {
            logger('VENDOR_APPROVE_FAILED', ['vendor' => $vendor, 'message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong']);
        }
    }

    public function reject($id) {
        $vendor = Vendor::find($id);

        if (empty($vendor)) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if ($vendor->status == Vendor::STATUS_REJECTED) {
            return response()->json(['message' => 'Vendor is already rejected']);
        }

        try {
            $vendor->update(['status' => Vendor::STATUS_REJECTED]);
            event(new VendorStatusUpdatedEvent($vendor));
            return response()->json(['message' => 'Vendor rejected successfully']);
        } catch (\Throwable $th) {
            logger('VENDOR_REJECT_FAILED', ['vendor' => $vendor, 'message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong']);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

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

        $vendor->update(['status' => Vendor::STATUS_APPROVED]);
        return response()->json(['message' => 'Vendor approved successfully']);
    }

    public function reject($id) {
        $vendor = Vendor::find($id);

        if (empty($vendor)) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->update(['status' => Vendor::STATUS_REJECTED]);
        return response()->json(['message' => 'Vendor rejected successfully']);
    }
}

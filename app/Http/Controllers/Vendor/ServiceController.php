<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function store(Request $request) {
        if (empty(auth()->user()->vendor)) {
            return response()->json(['User must be a vendor'], 403);
        }

        $request->validate([
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|string|max:255|'. Rule::unique('services')->where(function($query) {
                return $query->where('vendor_id', auth()->user()->vendor->id);
            }),
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'images' => 'required|array',
            'images.*.file' => 'required|image|mimetypes:image/jpeg,image/png,image/webp|max:2048',
            'images.*.alt_text' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            $service = Service::create([
                'vendor_id' => auth()->user()->vendor->id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . substr(uniqid(), -6),
                'short_description' => $request->short_description,
                'description' => $request->description,
                'price' => $request->price,
                'status' => Service::STATUS_ACTIVE
            ]);

            foreach ($request->images as $image) {
                $path = $image['file']->store('vendors/' . auth()->id(), 'public');
                ServiceImage::create([
                    'service_id' => $service->id,
                    'path' => $path,
                    'alt_text' => $image['alt_text']
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Service created successfully',
                'service' => $service->load('service_images')
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            logger('SERVICE_CREATION_FAILED', ['payload' => $request->all(), 'message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json([
                'message' => 'Something went wrong.',
            ], 500);
        }
    }
}

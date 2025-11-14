<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index() {
        $categories = ServiceCategory::paginate(10);
        return response()->json($categories);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100|unique:service_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = ServiceCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name, '-'),
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ]);
    }

    public function show($id) {
        $category = ServiceCategory::find($id);

        if (empty($category)) {
            return response()->json(['message' => 'Service category not found']);
        }

        return response()->json($category);
    }

    public function destroy($id) {
        $category = ServiceCategory::find($id);

        if (empty($category)) {
            return response()->json(['message' => 'Service category not found']);
        }

        $category->delete();

        return response()->json(['message' => 'Service category deleted successfully']);
    }
}

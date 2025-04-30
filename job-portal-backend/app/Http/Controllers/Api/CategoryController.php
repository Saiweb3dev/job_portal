<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function jobs($id)
    {
        $category = Category::with('jobs')->findOrFail($id);
        return response()->json([
            'category' => $category->name,
            'jobs' => $category->jobs,
        ]);
    }
}

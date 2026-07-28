<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'categories' => Category::where('cat_active', true)->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            ...$request->validated(),
            'cat_slug' => Str::slug($request->validated('cat_nome')),
        ]);

        return response()->json(['category' => $category], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        if ($request->has('cat_nome')) {
            $category->update(['cat_slug' => Str::slug($category->cat_nome)]);
        }

        return response()->json(['category' => $category]);
    }

    public function destroy(Category $category)
    {
        $category->update(['cat_active' => false]);

        return response()->json(['message' => 'Categoria desativada.']);
    }
}

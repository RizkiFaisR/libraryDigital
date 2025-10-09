<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('books');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($categories);
        }

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255','unique:categories,name'],
            'description' => ['nullable','string'],
        ]);

        $category = Category::create($data);
        if ($request->wantsJson()) {
            return response()->json(['message'=>'created','category'=>$category],201);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json($category);
        }

        return redirect()->back()->with('success', 'Category loaded');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes','required','string','max:255','unique:categories,name,'.$category->id],
            'description' => ['nullable','string'],
        ]);

        $category->update($data);

        if ($request->wantsJson()) {
            return response()->json(['message'=>'updated','category'=>$category]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        if (request()->wantsJson()) {
            return response()->json(null,204);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }
}

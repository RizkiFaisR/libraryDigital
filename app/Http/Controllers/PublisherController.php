<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Publisher;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $query = Publisher::withCount('books');

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $publishers = $query->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($publishers);
        }

        return view('publishers.index', compact('publishers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255','unique:publishers,name'],
            'address' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
        ]);

        $publisher = Publisher::create($data);
        if ($request->wantsJson()) {
            return response()->json(['message'=>'created','publisher'=>$publisher],201);
        }

        return redirect()->route('publishers.index')->with('success', 'Publisher created successfully');
    }

    public function show($id)
    {
        $publisher = Publisher::findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json($publisher);
        }

        return redirect()->back()->with('success', 'Publisher loaded');
    }

    public function update(Request $request, $id)
    {
        $publisher = Publisher::findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes','required','string','max:255','unique:publishers,name,'.$publisher->id],
            'address' => ['nullable','string','max:255'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:255'],
        ]);

        $publisher->update($data);

        if ($request->wantsJson()) {
            return response()->json(['message'=>'updated','publisher'=>$publisher]);
        }

        return redirect()->route('publishers.index')->with('success', 'Publisher updated successfully');
    }

    public function destroy($id)
    {
        $publisher = Publisher::findOrFail($id);
        $publisher->delete();
        if (request()->wantsJson()) {
            return response()->json(null,204);
        }

        return redirect()->route('publishers.index')->with('success', 'Publisher deleted successfully');
    }
}

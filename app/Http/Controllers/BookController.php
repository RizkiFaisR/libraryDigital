<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['category','publisher']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('publisher_id') && $request->publisher_id) {
            $query->where('publisher_id', $request->publisher_id);
        }

        if ($request->has('availability') && $request->availability) {
            if ($request->availability === 'available') {
                $query->where('copies', '>', 0);
            } elseif ($request->availability === 'borrowed') {
                $query->where('copies', 0);
            }
        }

        $books = $query->orderBy('id','desc')->paginate(12);
        $categories = Category::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();

        // if an authenticated user, compute which books they already have active borrowings for
        $userBorrowedBookIds = [];
        if (Auth::check()) {
            $userBorrowedBookIds = \App\Models\Borrowing::where('user_id', Auth::id())
                ->whereIn('status', ['pending','borrowed','overdue'])->pluck('book_id')->toArray();
        }

        if ($request->wantsJson()) {
            return response()->json($books);
        }

    return view('books.index', compact('books', 'categories', 'publishers', 'userBorrowedBookIds'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'isbn' => ['nullable','string','max:255','unique:books,isbn'],
            'author' => ['nullable','string','max:255'],
            'year' => ['nullable','integer'],
            'category_id' => ['required','exists:categories,id'],
            'publisher_id' => ['required','exists:publishers,id'],
            'copies' => ['nullable','integer','min:0'],
            'description' => ['nullable','string'],
        ]);

        // handle optional cover upload
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('books/covers', 'public');
            $data['cover'] = $path;
        }

        $book = Book::create($data);
        if ($request->wantsJson()) {
            return response()->json(['message'=>'created','book'=>$book],201);
        }

        return redirect()->route('books.index')->with('success', 'Book created successfully');
    }

    public function show($id)
    {
        $book = Book::with(['category','publisher'])->findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json($book);
        }

        return redirect()->back()->with('success', 'Book loaded');
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $data = $request->validate([
            'title' => ['sometimes','required','string','max:255'],
            'isbn' => ['nullable','string','max:255','unique:books,isbn,'.$book->id],
            'author' => ['nullable','string','max:255'],
            'year' => ['nullable','integer'],
            'category_id' => ['required','exists:categories,id'],
            'publisher_id' => ['required','exists:publishers,id'],
            'copies' => ['nullable','integer','min:0'],
            'description' => ['nullable','string'],
        ]);

        // handle cover replacement
        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('books/covers', 'public');
            $data['cover'] = $path;
            // optionally: delete old cover file
            if ($book->cover) {
                try { Storage::disk('public')->delete($book->cover); } catch (\Throwable $e) { /* ignore */ }
            }
        }

        $book->update($data);

        if ($request->wantsJson()) {
            return response()->json(['message'=>'updated','book'=>$book]);
        }

        return redirect()->route('books.index')->with('success', 'Book updated successfully');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();
        if (request()->wantsJson()) {
            return response()->json(null,204);
        }

        return redirect()->route('books.index')->with('success', 'Book deleted successfully');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Borrowing;
use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user','book']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function($userQuery) use ($request) {
                    $userQuery->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('email', 'like', '%' . $request->search . '%');
                })->orWhereHas('book', function($bookQuery) use ($request) {
                    $bookQuery->where('title', 'like', '%' . $request->search . '%')
                             ->orWhere('author', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $borrowings = $query->orderBy('id','desc')->paginate(15);

        $users = User::orderBy('name')->get();

        // compute simple stats for current dataset (overall counts)
        $stats = [
            'pending' => Borrowing::where('status', 'pending')->count(),
            'borrowed' => Borrowing::where('status', 'borrowed')->count(),
            'returned' => Borrowing::where('status', 'returned')->count(),
            'overdue' => Borrowing::where('status', 'overdue')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json($borrowings);
        }

        return view('borrowings.index', compact('borrowings', 'users', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required','exists:users,id'],
            'book_id' => ['required','exists:books,id'],
            'due_at' => ['nullable','date'],
        ]);
        // Prevent creating duplicate active borrowings for the same user & book
        $existing = Borrowing::where('user_id', $data['user_id'])
            ->where('book_id', $data['book_id'])
            ->whereIn('status', ['pending','borrowed','overdue'])
            ->exists();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'User already has an active borrowing for this book'], 422);
            }

            return redirect()->route('borrowings.index')->withErrors('User already has an active borrowing for this book');
        }

        // Admin/operator creating a borrowing: mark as borrowed immediately and decrement copies atomically
        $book = Book::findOrFail($data['book_id']);
        if ($book->copies <= 0) {
            return redirect()->route('borrowings.index')->withErrors('No copies available for this book');
        }

        DB::transaction(function() use ($data, &$borrowing) {
            $borrowing = Borrowing::create([
                'user_id' => $data['user_id'],
                'book_id' => $data['book_id'],
                'due_at' => $data['due_at'] ?? null,
                'status' => 'borrowed',
                'borrowed_at' => now(),
            ]);

            Book::where('id', $data['book_id'])->decrement('copies', 1);
        });
        if ($request->wantsJson()) {
            return response()->json(['message'=>'created','borrowing'=>$borrowing],201);
        }

        return redirect()->route('borrowings.index')->with('success', 'Borrowing created successfully');
    }

    public function show($id)
    {
        $borrowing = Borrowing::with(['user','book'])->findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json($borrowing);
        }

        return redirect()->back()->with('success', 'Borrowing loaded');
    }

    public function update(Request $request, $id)
    {
        $borrowing = Borrowing::findOrFail($id);

        $data = $request->validate([
            'returned_at' => ['nullable','date'],
            'status' => ['nullable','in:pending,borrowed,returned,overdue'],
        ]);

        $prevStatus = $borrowing->status;

        DB::transaction(function() use ($borrowing, $data, $prevStatus) {
            $borrowing->update($data);

            $newStatus = $borrowing->status;

            // if transition to 'borrowed' from non-borrowed, decrement copies
            if ($prevStatus !== 'borrowed' && $newStatus === 'borrowed') {
                $book = Book::where('id', $borrowing->book_id)->lockForUpdate()->first();
                if ($book->copies <= 0) {
                    throw new \Exception('No copies available to mark as borrowed');
                }
                $book->decrement('copies', 1);
                $borrowing->update(['borrowed_at' => $borrowing->borrowed_at ?? now()]);
            }

            // if transition to 'returned' from non-returned, increment copies
            if ($prevStatus !== 'returned' && $newStatus === 'returned') {
                Book::where('id', $borrowing->book_id)->increment('copies', 1);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['message'=>'updated','borrowing'=>$borrowing]);
        }

        return redirect()->route('borrowings.index')->with('success', 'Borrowing updated successfully');
    }

    public function destroy($id)
    {
        $borrowing = Borrowing::findOrFail($id);
        // if record represented an active borrowed book, restore copies
        if ($borrowing->status === 'borrowed') {
            Book::where('id', $borrowing->book_id)->increment('copies', 1);
        }
        $borrowing->delete();
        if (request()->wantsJson()) {
            return response()->json(null,204);
        }

        return redirect()->route('borrowings.index')->with('success', 'Borrowing deleted successfully');
    }

    public function borrowBook(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required','exists:books,id'],
            'due_at' => ['nullable','date','after:today'],
        ]);

        // Prevent user from requesting the same book multiple times (pending/borrowed/overdue)
        $existing = Borrowing::where('user_id', Auth::id())
            ->where('book_id', $data['book_id'])
            ->whereIn('status', ['pending','borrowed','overdue'])
            ->exists();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You already have an active borrowing for this book'], 422);
            }

            return redirect()->route('user.borrowings')->withErrors('You already have an active borrowing for this book');
        }

        // create a pending request; copies are changed only on approve
        $borrowing = Borrowing::create([
            'user_id' => Auth::id(),
            'book_id' => $data['book_id'],
            'due_at' => $data['due_at'] ?? now()->addDays(7),
            'status' => 'pending',
        ]);
        if ($request->wantsJson()) {
            return response()->json(['message'=>'created','borrowing'=>$borrowing],201);
        }

        return redirect()->route('user.borrowings')->with('success', 'Borrowing request submitted');
    }

    public function myBorrowings(Request $request)
    {
        $query = Borrowing::with(['book'])
            ->where('user_id', Auth::id());

        if ($request->has('search') && $request->search) {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->orderBy('id','desc')->paginate(15);
        if ($request->wantsJson()) {
            return response()->json($borrowings);
        }

        return view('user.borrowings', compact('borrowings'));
    }

    public function approve($id)
    {
        $borrowing = Borrowing::findOrFail($id);
        // decrement book copies and mark as borrowed atomically
        $book = Book::findOrFail($borrowing->book_id);
        if ($book->copies <= 0) {
            return redirect()->route('borrowings.index')->withErrors('No copies available to approve this borrowing');
        }

        DB::transaction(function() use ($borrowing) {
            Book::where('id', $borrowing->book_id)->decrement('copies', 1);
            $borrowing->update(['status' => 'borrowed', 'borrowed_at' => now()]);
        });
        if (request()->wantsJson()) {
            return response()->json(['message'=>'approved','borrowing'=>$borrowing]);
        }

        return redirect()->route('borrowings.index')->with('success', 'Borrowing approved');
    }

    public function reject($id)
    {
        $borrowing = Borrowing::findOrFail($id);
        $borrowing->delete();
        if (request()->wantsJson()) {
            return response()->json(['message'=>'rejected'],204);
        }

        return redirect()->route('borrowings.index')->with('success', 'Borrowing rejected');
    }
}

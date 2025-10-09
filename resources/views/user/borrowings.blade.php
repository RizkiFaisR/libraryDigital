@extends('layouts.app')

@section('title', 'My Borrowings')
@section('page-title', 'My Borrowings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Borrowings</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track your borrowed books and their status</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="returned">Returned</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div>
                <label for="search_books" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Books</label>
                <input type="text" id="search_books" placeholder="Search by book title..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 w-full">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Borrowings List -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <ul id="borrowings-list" class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($borrowings as $borrowing)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                    {{ $borrowing->book->title }}
                                </h3>
                                <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                                    @if($borrowing->book->author)
                                        <p><span class="font-medium">Author:</span> {{ $borrowing->book->author }}</p>
                                    @endif
                                    @if($borrowing->book->isbn)
                                        <p><span class="font-medium">ISBN:</span> {{ $borrowing->book->isbn }}</p>
                                    @endif
                                    <p><span class="font-medium">Borrowed:</span> {{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('M d, Y') : 'Not set' }}</p>
                                    @if($borrowing->due_at)
                                        <p><span class="font-medium">Due:</span> {{ $borrowing->due_at ? $borrowing->due_at->format('M d, Y') : 'Not set' }}</p>
                                    @endif
                                    @if($borrowing->returned_at)
                                        <p><span class="font-medium">Returned:</span> {{ $borrowing->returned_at ? $borrowing->returned_at->format('M d, Y') : 'Not set' }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'borrowed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    'returned' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'overdue' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                ];
                                $statusText = [
                                    'pending' => 'Pending Approval',
                                    'borrowed' => 'Borrowed',
                                    'returned' => 'Returned',
                                    'overdue' => 'Overdue'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$borrowing->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusText[$borrowing->status] ?? ucfirst($borrowing->status) }}
                            </span>

                            @if($borrowing->status === 'overdue')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Overdue
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($borrowing->book->description)
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $borrowing->book->description }}</p>
                        </div>
                    @endif
                </li>
            @empty
                <li class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No borrowings found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You haven't borrowed any books yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Browse Books
                        </a>
                    </div>
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    @if($borrowings->hasPages())
        <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing {{ $borrowings->firstItem() ?? 0 }} to {{ $borrowings->lastItem() ?? 0 }} of {{ $borrowings->total() }} results
            </div>
            <div class="flex space-x-1">
                @if($borrowings->onFirstPage())
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md cursor-not-allowed">
                        Previous
                    </span>
                @else
                    <a href="{{ $borrowings->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Previous
                    </a>
                @endif

                @foreach($borrowings->getUrlRange(max(1, $borrowings->currentPage() - 2), min($borrowings->lastPage(), $borrowings->currentPage() + 2)) as $page => $url)
                    @if($page == $borrowings->currentPage())
                        <span class="px-3 py-2 text-sm font-medium text-white bg-primary-600 border-primary-600 border rounded-md">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($borrowings->hasMorePages())
                    <a href="{{ $borrowings->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Next
                    </a>
                @else
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md cursor-not-allowed">
                        Next
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// My Borrowings page is server-rendered and uses Laravel pagination and filters.
function showError(message) { if (window.showError) return window.showError(message); alert('Error: ' + message); }
</script>
@endpush

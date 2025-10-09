@extends('layouts.app')

@section('title', 'Books')
@section('page-title', 'Books Management')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Books</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your library collection</p>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'operator']))
            <div class="mt-4 sm:mt-0">
                <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Book
                </button>
            </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
          <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
          <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search books..."
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
            </div>
            <div>
                <label for="category_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select id="category_filter" name="category_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="publisher_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publisher</label>
                <select id="publisher_filter" name="publisher_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All Publishers</option>
                    @foreach($publishers ?? [] as $publisher)
                        <option value="{{ $publisher->id }}" {{ request('publisher_id') == $publisher->id ? 'selected' : '' }}>{{ $publisher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="availability_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Availability</label>
                <select id="availability_filter" name="availability" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">All Books</option>
                    <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="borrowed" {{ request('availability') == 'borrowed' ? 'selected' : '' }}>Currently Borrowed</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <button onclick="clearFilters()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear Filters
            </button>
            <button onclick="applyFilters()" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Apply Filters
            </button>
        </div>
    </div>

    <!-- Books Grid -->
    <div id="books-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($books as $book)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    @if($book->cover_url)
                        <div class="mb-4">
                            <img src="{{ $book->cover_url }}" alt="Cover for {{ $book->title }}" class="w-full h-48 object-cover rounded">
                        </div>
                    @endif
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $book->title }}</h3>
                        @if($book->copies > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Available</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Borrowed</span>
                        @endif
                    </div>

                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        @if($book->author)
                            <p><span class="font-medium">Author:</span> {{ $book->author }}</p>
                        @endif
                        @if($book->year)
                            <p><span class="font-medium">Year:</span> {{ $book->year }}</p>
                        @endif
                        @if($book->isbn)
                            <p><span class="font-medium">ISBN:</span> {{ $book->isbn }}</p>
                        @endif
                        @if($book->category)
                            <p><span class="font-medium">Category:</span> {{ $book->category->name }}</p>
                        @endif
                        @if($book->publisher)
                            <p><span class="font-medium">Publisher:</span> {{ $book->publisher->name }}</p>
                        @endif
                        <p><span class="font-medium">Copies:</span> {{ $book->copies }}</p>
                    </div>

                    @if($book->description)
                        <div class="mt-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $book->description }}</p>
                        </div>
                    @endif

                    <div class="mt-4 flex justify-between items-center">
                        @if(auth()->user()->role === 'user')
                            @php $already = in_array($book->id, $userBorrowedBookIds ?? []); @endphp
                            <button onclick="borrowBook({{ $book->id }})"
                                    {{ ($book->copies === 0 || $already) ? 'disabled' : '' }}
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                @if($already)
                                    Already Borrowed
                                @else
                                    Borrow
                                @endif
                            </button>
                        @else
                            <div class="flex space-x-2">
                                <button onclick="editBook({{ $book->id }})" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-xs font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>
                                <button onclick="deleteBook({{ $book->id }})" class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-600 text-xs font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No books found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a new book.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($books->hasPages())
        <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Showing {{ $books->firstItem() ?? 0 }} to {{ $books->lastItem() ?? 0 }} of {{ $books->total() }} results
            </div>
            <div class="flex space-x-1">
                @if($books->onFirstPage())
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md cursor-not-allowed">
                        Previous
                    </span>
                @else
                    <a href="{{ $books->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Previous
                    </a>
                @endif

                @foreach($books->getUrlRange(max(1, $books->currentPage() - 2), min($books->lastPage(), $books->currentPage() + 2)) as $page => $url)
                    @if($page == $books->currentPage())
                        <span class="px-3 py-2 text-sm font-medium text-white bg-primary-600 border-primary-600 border rounded-md">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($books->hasMorePages())
                    <a href="{{ $books->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
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

<!-- Create/Edit Book Modal -->
<div id="book-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="book-form" method="POST" action="{{ route('books.store') }}" data-default-action="{{ route('books.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="book-form-method" value="POST">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">
                                Add New Book
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                    <input type="text" id="title" name="title" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="isbn" class="block text-sm font-medium text-gray-700 dark:text-gray-300">ISBN</label>
                                    <input type="text" id="isbn" name="isbn"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="author" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Author</label>
                                    <input type="text" id="author" name="author"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                                    <input type="number" id="year" name="year"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category <span class="text-red-500">*</span></label>
                                    <select id="category_id" name="category_id" required aria-required="true"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option value="" disabled selected hidden>Select Category</option>
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="publisher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Publisher <span class="text-red-500">*</span></label>
                                    <select id="publisher_id" name="publisher_id" required aria-required="true"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                        <option value="" disabled selected hidden>Select Publisher</option>
                                        @foreach($publishers ?? [] as $publisher)
                                            <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="copies" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Copies</label>
                                    <input type="number" id="copies" name="copies" min="0"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea id="description" name="description" rows="3"
                                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label for="cover" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cover Image</label>
                                    <input type="file" id="cover" name="cover" accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Server-rendered books listing. Minimal client helpers remain for modals and toast delegation.
function showError(message) { if (window.showError) return window.showError(message); alert('Error: ' + message); }
function showSuccess(message) { if (window.showSuccess) return window.showSuccess(message); alert(message); }
function openCreateModal() { document.getElementById('modal-title').textContent = 'Add New Book'; document.getElementById('book-form').reset(); document.getElementById('book-modal').classList.remove('hidden'); }
function closeModal() { document.getElementById('book-modal').classList.add('hidden'); }
// Edit and delete helpers
const booksStoreUrl = "{{ route('books.store') }}";
const booksBaseUrl = "{{ url('books') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function editBook(id) {
    fetch(`${booksBaseUrl}/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            const book = data;
            document.getElementById('modal-title').textContent = 'Edit Book';
            const form = document.getElementById('book-form');
            form.action = `${booksBaseUrl}/${id}`;
            // ensure method spoof field exists
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden'; methodField.name = '_method'; methodField.id = 'book-form-method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';
            // ensure CSRF exists
            if (!form.querySelector('input[name="_token"]')) {
                const token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = csrfToken; form.appendChild(token);
            }
            // populate fields
            ['title','isbn','author','year','category_id','publisher_id','copies','description'].forEach(k => {
                const el = document.getElementById(k);
                if (!el) return;
                if (el.tagName.toLowerCase() === 'select' || el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
                    el.value = book[k] ?? '';
                }
            });
            document.getElementById('book-modal').classList.remove('hidden');
        }).catch(err => showError('Failed to load book data'));
}

function deleteBook(id) {
    if (!confirm('Delete this book?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${booksBaseUrl}/${id}`;
    const token = document.createElement('input');
    token.type = 'hidden'; token.name = '_token'; token.value = csrfToken; form.appendChild(token);
    const method = document.createElement('input');
    method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE'; form.appendChild(method);
    document.body.appendChild(form);
    form.submit();
}

// Borrow helper for users: submit a small POST form to request a borrow
function borrowBook(bookId) {
    if (!confirm('Submit a borrow request for this book?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('user.borrow') }}";
    const token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = csrfToken; form.appendChild(token);
    const input = document.createElement('input'); input.type = 'hidden'; input.name = 'book_id'; input.value = bookId; form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Ensure method spoofing is present before submit
document.addEventListener('DOMContentLoaded', function() {
    const bookForm = document.getElementById('book-form');
    if (bookForm) {
        bookForm.addEventListener('submit', function() {
            let methodField = bookForm.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden'; methodField.name = '_method'; bookForm.appendChild(methodField);
            }
            // If the action looks like /books/{id} treat as edit -> spoof PUT
            try {
                const action = (bookForm.action || '').replace(/\/$/, '');
                if (/\/books\/\d+$/.test(action)) {
                    methodField.value = 'PUT';
                } else {
                    methodField.value = 'POST';
                }
            } catch (e) {
                if (!methodField.value) methodField.value = 'POST';
            }
        });
    }
});

// Filters: apply and clear using server-side query params
function applyFilters() {
    const search = document.getElementById('search').value || '';
    const category = document.getElementById('category_filter').value || '';
    const publisher = document.getElementById('publisher_filter').value || '';
    const availability = document.getElementById('availability_filter').value || '';
    const params = new URLSearchParams(window.location.search);
    if (search) params.set('search', search); else params.delete('search');
    if (category) params.set('category_id', category); else params.delete('category_id');
    if (publisher) params.set('publisher_id', publisher); else params.delete('publisher_id');
    if (availability) params.set('availability', availability); else params.delete('availability');
    const qs = params.toString();
    window.location = window.location.pathname + (qs ? ('?' + qs) : '');
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('category_filter').value = '';
    document.getElementById('publisher_filter').value = '';
    document.getElementById('availability_filter').value = '';
    const params = new URLSearchParams(window.location.search);
    params.delete('search'); params.delete('category_id'); params.delete('publisher_id'); params.delete('availability');
    const qs = params.toString();
    window.location = window.location.pathname + (qs ? ('?' + qs) : '');
}
</script>
@endpush

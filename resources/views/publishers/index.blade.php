@extends('layouts.app')

@section('title', 'Publishers')
@section('page-title', 'Publishers Management')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Publishers</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage book publishers and their information</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Publisher
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="max-w-md">
            <label for="search_publishers" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Publishers</label>
            <input type="text" id="search_publishers" name="search" value="{{ request('search') }}" placeholder="Search publishers..."
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
            <div class="mt-3 flex space-x-2">
                <button type="button" onclick="clearPublisherFilters()" class="px-3 py-1 rounded-md border bg-white dark:bg-gray-700">Clear</button>
                <button type="button" onclick="applyPublisherFilters()" class="px-3 py-1 rounded-md bg-primary-600 text-white">Apply</button>
            </div>
        </div>
    </div>

    <!-- Publishers Grid -->
    <div id="publishers-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($publishers as $publisher)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $publisher->name }}</h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="editPublisher({{ $publisher->id }})" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deletePublisher({{ $publisher->id }})" class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-300 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        @if($publisher->address)
                            <p><span class="font-medium">Address:</span> {{ $publisher->address }}</p>
                        @endif
                        @if($publisher->phone)
                            <p><span class="font-medium">Phone:</span> {{ $publisher->phone }}</p>
                        @endif
                        @if($publisher->email)
                            <p><span class="font-medium">Email:</span> {{ $publisher->email }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>Created: {{ $publisher->created_at->format('M d, Y') }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $publisher->books_count ?? 0 }} books
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No publishers found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new publisher.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Create/Edit Publisher Modal -->
<div id="publisher-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="publisher-form" method="POST" action="{{ route('publishers.store') }}" data-default-action="{{ route('publishers.store') }}">
                @csrf
                <input type="hidden" name="_method" id="publisher-form-method" value="POST">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">
                                Add New Publisher
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Publisher Name</label>
                                    <input type="text" id="name" name="name" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                    <textarea id="address" name="address" rows="3"
                                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"></textarea>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <input type="tel" id="phone" name="phone"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" id="email" name="email"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
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
// Publishers listing is server-rendered now. Keep minimal helpers for modal interactions.
function showError(message) { if (window.showError) return window.showError(message); alert('Error: ' + message); }
function openCreateModal() { document.getElementById('modal-title').textContent = 'Add New Publisher'; document.getElementById('publisher-form').reset(); document.getElementById('publisher-modal').classList.remove('hidden'); }
function closeModal() { document.getElementById('publisher-modal').classList.add('hidden'); }
// edit/delete helpers
const publishersBaseUrl = "{{ url('publishers') }}";
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function editPublisher(id) {
    fetch(`${publishersBaseUrl}/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            document.getElementById('modal-title').textContent = 'Edit Publisher';
            const form = document.getElementById('publisher-form');
            form.action = `${publishersBaseUrl}/${id}`;
            document.getElementById('publisher-form-method').value = 'PUT';
            document.getElementById('name').value = data.name || '';
            document.getElementById('address').value = data.address || '';
            document.getElementById('phone').value = data.phone || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('publisher-modal').classList.remove('hidden');
        }).catch(() => showError('Failed to load publisher'));
}

function deletePublisher(id) {
    if (!confirm('Delete this publisher?')) return;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `${publishersBaseUrl}/${id}`;
    form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(form); form.submit();
}

function applyPublisherFilters() {
    const q = document.getElementById('search_publishers').value || '';
    const params = new URLSearchParams(window.location.search);
    if (q) params.set('search', q); else params.delete('search');
    window.location = window.location.pathname + '?' + params.toString();
}

function clearPublisherFilters() {
    document.getElementById('search_publishers').value = '';
    const params = new URLSearchParams(window.location.search);
    params.delete('search');
    window.location = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
}
</script>
@endpush

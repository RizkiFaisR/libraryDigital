@extends('layouts.app')

@section('hideSidebar', '1') {{-- tambahkan ini supaya layout tidak menampilkan sidebar di halaman welcome --}}

@section('title', 'Welcome')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
	<div class="max-w-2xl w-full text-center space-y-6">
		<div>
			<div class="mx-auto h-16 w-16 bg-primary-600 rounded-lg flex items-center justify-center">
				<svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
				</svg>
			</div>
			<h1 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Library Digital</h1>
			<p class="mt-2 text-sm text-gray-600 dark:text-gray-300">A lightweight library app. Sign in to manage or borrow books.</p>
		</div>

		<div class="flex items-center justify-center space-x-4">
			@guest
				<a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 rounded-md bg-primary-600 text-white font-medium hover:bg-primary-700">Login</a>
				<a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">Register</a>
			@else
				<a href="{{ route('books.index') }}" class="inline-flex items-center px-6 py-3 rounded-md bg-primary-600 text-white font-medium hover:bg-primary-700">Go to Books</a>
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					<button type="submit" class="inline-flex items-center px-6 py-3 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">Logout</button>
				</form>
			@endguest
		</div>
	</div>
</div>
@endsection


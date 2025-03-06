@extends('layouts.app') 



<div class="flex items-center">
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="px-4 py-2">
        {{ __('Log Out') }}
    </button>
</form>
</div>

@section('header')
    <h1>Welcome to the Home Page</h1>
@endsection

@section('content')
    <p>This is the content of the home page.</p>
@endsection
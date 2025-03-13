@extends('layouts.app') 



<div class="flex items-center">
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="px-6 py-4">
        {{ __('Iziet') }}
    </button>
</form>
</div>

@section('header')
    <h1>Welcome to the Home Page</h1>
@endsection

@section('content')
    <p>Kautkada informacija seit!!!!</p>
@endsection
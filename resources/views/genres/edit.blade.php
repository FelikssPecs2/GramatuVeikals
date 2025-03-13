@extends('layouts.app')

@section('content')
    <h1>Edit Genre</h1>

    <form action="{{ route('genres.update', $genre->id) }}" method="POST"> 
        @csrf
        @method('PUT') 

        <div class="form-group">
            <label for="name">Genre Name:</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $genre->name }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
@endsection
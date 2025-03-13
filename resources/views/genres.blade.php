@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Žanri</h1>

        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="#" class="btn btn-primary" onclick="showCreateForm()">Pievienojiet žanru
</a>
        @endif

        {{-- Genre List Table --}}
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Žanra nosaukums</th>
                    
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <th>Darbības</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($genres as $genre)  <!-- Loop through $genres, not $authors -->
                    <tr>
                        <td>{{ $genre->name }}</td>
                        {{-- Show Edit/Delete buttons only for admins --}}
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <td>
                                <a href="#" class="btn btn-warning" onclick="showEditForm('{{ $genre->id }}', '{{ $genre->name }}')">Rediģēt</a>
                                <form action="{{ route('genres.destroy', $genre) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Dzēst</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Create Genre Form (Hidden by Default) --}}
        <div id="createGenreForm" class="mt-4" style="display: none;">
            <h2>Pievienot Žanru</h2>
            <form action="{{ route('genres.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Žanra nosaukums:</label>
                    <input type="text" name="name" id="name" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Veidot</button>
                <button type="button" class="btn btn-secondary" onclick="hideForms()">Atcelt
</button>
            </form>
        </div>

        {{-- Edit Genre Form (Hidden by Default) --}}
        <div id="editGenreForm" class="mt-4" style="display: none;">
            <h2>Rediģēt žanru</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editName">Žanra nosaukums:</label>
                    <input type="text" name="name" id="editName" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Atjaunināt</button>
                <button type="button" class="btn btn-secondary" onclick="hideForms()">Atcelt
</button>
            </form>
        </div>
    </div>

    {{-- JavaScript to Show/Hide Forms Dynamically --}}
    <script>
        function showCreateForm() {
            document.getElementById('createGenreForm').style.display = 'block';
            document.getElementById('editGenreForm').style.display = 'none';
        }

        function showEditForm(id, name) {
            document.getElementById('editGenreForm').style.display = 'block';
            document.getElementById('createGenreForm').style.display = 'none';
            document.getElementById('editName').value = name;
            document.getElementById('editForm').action = '/genres/' + id;
        }

        function hideForms() {
            document.getElementById('createGenreForm').style.display = 'none';
            document.getElementById('editGenreForm').style.display = 'none';
        }
    </script>
@endsection

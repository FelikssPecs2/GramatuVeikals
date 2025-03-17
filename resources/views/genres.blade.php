@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Žanri</h1>

        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="#" class="btn btn-primary" onclick="showGenreModal()">Pievienojiet žanru</a>
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
                @foreach($genres as $genre)
                    <tr>
                        <td>{{ $genre->name }}</td>
                        {{-- Show Edit/Delete buttons only for admins --}}
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <td>
                                <a href="#" class="btn btn-warning" onclick="showGenreModal('{{ $genre->id }}', '{{ $genre->name }}')">Rediģēt</a>
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

        {{-- Genre Modal --}}
        <div id="genreModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; padding: 20px; border-radius: 10px; text-align: center; max-width: 400px;">
                <h2 id="genreFormTitle">Pievienot žanru</h2>
                <form id="genreFormElement" action="{{ route('genres.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="genreFormMethod" value="POST">
                    <div class="form-group">
                        <label for="name">Žanra nosaukums:</label>
                        <input type="text" name="name" id="name" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Saglabāt</button>
                    <button type="button" class="btn btn-secondary" onclick="hideGenreModal()">Atcelt</button>
                </form>
            </div>
        </div>
    </div>

    {{-- JavaScript to Show/Hide the Modal --}}
    <script>
        function showGenreModal(id = null, name = '') {
            document.getElementById('genreModal').style.display = 'flex';
            document.getElementById('genreFormTitle').innerText = id ? 'Rediģēt žanru' : 'Pievienot žanru';
            document.getElementById('genreFormElement').action = id ? '/genres/' + id : '{{ route('genres.store') }}';
            document.getElementById('genreFormMethod').value = id ? 'PUT' : 'POST';
            document.getElementById('name').value = name;
        }

        function hideGenreModal() {
            document.getElementById('genreModal').style.display = 'none';
        }
    </script>
@endsection
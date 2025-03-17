@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Autori</h1>

        {{-- Rādīt "Pievienot autoru" pogu tikai administratoriem --}}
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="#" class="btn btn-primary" onclick="showAuthorModal()">Pievienot autoru</a>
        @endif

        {{-- Autoru saraksta tabula --}}
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Autora vārds</th>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <th>Darbības</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($authors as $author)
                    <tr>
                        <td>{{ $author->name }}</td>
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <td>
                                <a href="#" class="btn btn-warning" onclick="showAuthorModal('{{ $author->id }}', '{{ $author->name }}')">Rediģēt</a>
                                <form action="{{ route('authors.destroy', $author) }}" method="POST" style="display:inline;">
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

        {{-- Modālais logs autora pievienošanai/rediģēšanai --}}
        <div id="authorModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; padding: 20px; border-radius: 10px; text-align: center; max-width: 400px;">
                <h2 id="authorFormTitle">Izveidot autoru</h2>

                <form id="authorFormElement" action="{{ route('authors.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="authorFormMethod" value="POST">

                    <div class="form-group">
                        <label for="name">Autora vārds:</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Saglabāt</button>
                    <button type="button" class="btn btn-secondary" onclick="hideAuthorModal()">Atcelt</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAuthorModal(id = null, name = '') {
            document.getElementById('authorModal').style.display = 'flex';
            document.getElementById('authorFormTitle').innerText = id ? 'Rediģēt autoru' : 'Izveidot autoru';
            document.getElementById('authorFormElement').action = id ? '/authors/' + id : '{{ route('authors.store') }}';
            document.getElementById('authorFormMethod').value = id ? 'PUT' : 'POST';
            document.getElementById('name').value = name;
        }

        function hideAuthorModal() {
            document.getElementById('authorModal').style.display = 'none';
        }
    </script>
@endsection
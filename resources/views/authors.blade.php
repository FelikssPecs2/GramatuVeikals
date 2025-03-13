@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Autori</h1>

        {{-- Rādīt "Pievienot autoru" pogu tikai administratoriem --}}
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="#" class="btn btn-primary" onclick="showCreateForm()">Pievienot autoru</a>
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
                                <!-- Mainīta rediģēšanas poga, lai izmantotu JavaScript funkciju -->
                                <a href="#" class="btn btn-warning" onclick="showEditForm('{{ $author->id }}', '{{ $author->name }}')">Rediģēt</a>
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

        {{-- Autora izveides forma (sākotnēji paslēpta) --}}
        <div id="createAuthorForm" class="mt-4" style="display: none;">
            <h2>Izveidot autoru</h2>
            <form action="{{ route('authors.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Autora vārds:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Izveidot</button>
                <button type="button" class="btn btn-secondary" onclick="hideForms()">Atcelt</button>
            </form>
        </div>

        {{-- Autora rediģēšanas forma (sākotnēji paslēpta) --}}
        <div id="editAuthorForm" class="mt-4" style="display: none;">
            <h2>Rediģēt autoru</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editName">Autora vārds:</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Atjaunināt</button>
                <button type="button" class="btn btn-secondary" onclick="hideForms()">Atcelt</button>
            </form>
        </div>
    </div>

    <script>
        function showEditForm(id, name) {
            document.getElementById('editAuthorForm').style.display = 'block';
            document.getElementById('createAuthorForm').style.display = 'none';

            document.getElementById('editName').value = name;
            document.getElementById('editForm').action = '/authors/' + id;
        }

        function showCreateForm() {
            document.getElementById('createAuthorForm').style.display = 'block';
            document.getElementById('editAuthorForm').style.display = 'none';
        }

        function hideForms() {
            document.getElementById('createAuthorForm').style.display = 'none';
            document.getElementById('editAuthorForm').style.display = 'none';
        }
    </script>
@endsection

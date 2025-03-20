@extends('layouts.app')

@section('content')

    <div class="container mt-4">
        <h1>Grāmatas</h1>
            
        {{-- Rādīt "Pievienot grāmatu" pogu tikai administratoriem --}}
        @if(auth()->check() && auth()->user()-> isAdmin())
            <a href="#" class="btn btn-primary" onclick="showCreateForm()">Pievienot grāmatu</a>
        @endif
        <div class="container mt-4">

{{-- Search Form --}}
<form action="{{ route('books.index') }}" method="GET">
    <div class="row">
        <!-- Search by Book Name -->
        <div class="col-md-4">
            <label for="search">Meklēt pēc nosaukuma:</label>
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                value="{{ request('search') }}" 
                placeholder="Ievadiet grāmatas nosaukumu"
            >
        </div>

        <!-- Search by Author -->
        <div class="col-md-4">
            <label for="author">Meklēt pēc autora:</label>
            <select name="author" id="author" class="form-control">
                <option value="">Izvēlieties autoru</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                        {{ $author->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Filter by Genre -->
        <div class="col-md-4">
            <label for="genre">Filtrēt pēc žanra:</label>
            <select name="genre" id="genre" class="form-control">
                <option value="">Izvēlieties žanru</option>
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}" {{ request('genre') == $genre->id ? 'selected' : '' }}>
                        {{ $genre->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="row mt-3">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Meklēt</button>
            <a href="{{ route('books.index') }}" class="btn btn-secondary">Notīrīt filtrus</a>
        </div>
    </div>
</form>



{{-- Rest of your table and content --}}
<table class="table mt-4">
    <!-- Your table content here -->
</table>
</div>
        {{-- Grāmatu saraksta tabula --}}
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Nosaukums</th>
                    <th>Autors</th>
                    <th>Žanri</th>
                    <th>Cena</th> {{-- New price column --}}
                    <th>Darbības</th>
                </tr>
            </thead>
            <tbody>
                @foreach($books as $book)
                    <tr>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author->name }}</td>
                        <td>
                            @foreach($book->genres as $genre)
                                <span>{{ $genre->name }}</span>
                            @endforeach
                        </td>
                        <td>{{ $book->price }}</td> {{-- Display book price --}}
                        <td>
                            {{-- "Pirkt" poga redzama visiem lietotājiem --}}
                            <form action="{{ route('sales.store') }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <input type="hidden" name="sale_date" value="{{ now()->toDateString() }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-success">Pirkt</button>
                            </form>

                            {{-- Info poga --}}
                            <button type="button" class="btn btn-info" onclick="showInfoModal('{{ $book->id }}', '{{ $book->title }}', '{{ $book->author->name }}', '{{ implode(', ', $book->genres->pluck('name')->toArray()) }}', '{{ $book->price }}', '{{ $book->age }}', '{{ $book->pages }}', '{{ $book->description }}')">Info</button>

                            {{-- info poga redzam tikai administratoriem --}}
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <a href="#" class="btn btn-warning" onclick="showEditForm('{{ $book->id }}', '{{ $book->title }}', '{{ $book->author_id }}', '{{ $book->price }}', '{{ $book->age }}', '{{ $book->pages }}', '{{ $book->description }}', @json($book->genres->pluck('id')->toArray()))">Rediģēt</a> {{-- Include price in edit form --}}
                                <form action="{{ route('books.destroy', $book) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Dzēst</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <script>
            function showInfoModal(id, title, author, genres, price, age, pages, description) {
                document.getElementById('infoTitle').innerText = title;
                document.getElementById('infoAuthor').innerText = author;
                document.getElementById('infoGenres').innerText = genres;
                document.getElementById('infoPrice').innerText = price;
                document.getElementById('infoAge').innerText = age ? age : 'Nav norādīts';
                document.getElementById('infoPages').innerText = pages ? pages : 'Nav norādīts';
                document.getElementById('infoDescription').innerText = description ? description : 'Nav norādīts';
                document.getElementById('infoModal').style.display = 'flex';
            }

            function hideInfoModal() {
                document.getElementById('infoModal').style.display = 'none';
            }

            function showCreateForm() {
    // Open the modal
    openFormModal('Izveidot grāmatu', '{{ route('books.store') }}', 'POST');

    // Reset the form fields
    resetForm();

    // Add hidden inputs for page and filters
    const form = document.getElementById('bookFormElement');
    const pageInput = document.createElement('input');
    pageInput.type = 'hidden';
    pageInput.name = 'page';
    pageInput.value = new URLSearchParams(window.location.search).get('page') || 1;
    form.appendChild(pageInput);

    const searchInput = document.createElement('input');
    searchInput.type = 'hidden';
    searchInput.name = 'search';
    searchInput.value = new URLSearchParams(window.location.search).get('search') || '';
    form.appendChild(searchInput);

    const authorInput = document.createElement('input');
    authorInput.type = 'hidden';
    authorInput.name = 'author';
    authorInput.value = new URLSearchParams(window.location.search).get('author') || '';
    form.appendChild(authorInput);

    const genreInput = document.createElement('input');
    genreInput.type = 'hidden';
    genreInput.name = 'genre';
    genreInput.value = new URLSearchParams(window.location.search).get('genre') || '';
    form.appendChild(genreInput);
}

            function showEditForm(id, title, authorId, price, age, pages, description, selectedGenres) {
                openFormModal('Rediģēt grāmatu', '/books/' + id, 'PUT');
                populateForm(title, authorId, price, age, pages, description, selectedGenres);
            }


            function openFormModal(formTitle, formAction, formMethod) {
                document.getElementById('formModal').style.display = 'flex';
                document.getElementById('formTitle').innerText = formTitle;
                document.getElementById('bookFormElement').action = formAction;
                document.getElementById('formMethod').value = formMethod;
            }

            function closeFormModal() {
                document.getElementById('formModal').style.display = 'none';
            }

            function resetForm() {
                document.getElementById('title').value = '';
                document.getElementById('price').value = '';
                document.getElementById('age').value = '';
                document.getElementById('pages').value = '';
                document.getElementById('description').value = '';

            // Noņemt atzīmes no visiem checkboxiem
            document.querySelectorAll('input[name="genre_ids[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

            function populateForm(title, authorId, price, age, pages, description, selectedGenres) {
                document.getElementById('title').value = title;
                document.getElementById('author').value = authorId;
                document.getElementById('price').value = price;
                document.getElementById('age').value = age ? age : '';
                document.getElementById('pages').value = pages ? pages : '';
                document.getElementById('description').value = description ? description : '';

                // Atzīmēt atlasītos žanrus
                selectedGenres.forEach(genreId => {
                    document.querySelector(`input[name="genre_ids[]"][value="${genreId}"]`).checked = true;
                });
            }

            // Initialize Select2 for genres
            $(document).ready(function() {
                $('#genres').select2({
                    placeholder: "Izvēlieties žanrus",
                    multiple: true // Enable multi-select
                });
            });
        </script>

        <div id="infoModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; padding: 20px; border-radius: 10px; text-align: center; max-width: 400px;">
                <h4>Grāmatas Informācija</h4>

                <p><strong>Nosaukums:</strong> <span id="infoTitle"></span></p>
                <p><strong>Autors:</strong> <span id="infoAuthor"></span></p>
                <p><strong>Žanri:</strong> <span id="infoGenres"></span></p>
                <p><strong>Cena:</strong> <span id="infoPrice"></span>€</p>
                <p><strong>Vecums:</strong> <span id="infoAge"></span></p>
                <p><strong>Lappušu skaits:</strong> <span id="infoPages"></span></p>
                <p><strong>Apraksts:</strong> <span id="infoDescription"></span></p>

                <br>
                <button type="button" class="btn btn-secondary" onclick="hideInfoModal()">Aizvērt</button>
            </div>
        </div>

        {{-- Grāmatas izveides/rediģēšanas forma (sākotnēji paslēpta) --}}
        <div id="formModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; padding: 20px; border-radius: 10px; text-align: center; max-width: 550px;">
        <h2 id="formTitle">Izveidot grāmatu</h2>

        <form id="bookFormElement" action="{{ route('books.store') }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <!-- Hidden inputs for page and filters -->
            <input type="hidden" name="page" value="{{ request('page', 1) }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="author" value="{{ request('author') }}">
            <input type="hidden" name="genre" value="{{ request('genre') }}">

            <!-- Other form fields -->
            <div class="form-group">
                <label for="title">Grāmatas nosaukums:</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="author">Autors:</label>
                <select name="author_id" id="author" class="form-control" required>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Žanri:</label>
                <div>
                    @foreach($genres as $genre)
                        <div>
                            <input type="checkbox" name="genre_ids[]" id="genre_{{ $genre->id }}" value="{{ $genre->id }}">
                            <label for="genre_{{ $genre->id }}">{{ $genre->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="price">Cena:</label>
                <input type="number" name="price" id="price" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="age">Vecums:</label>
                <input type="number" name="age" id="age" class="form-control" value="{{ old('age') }}">
            </div>

            <div class="form-group">
                <label for="pages">Lappušu skaits:</label>
                <input type="number" name="pages" id="pages" class="form-control" value="{{ old('pages') }}">
            </div>

            <div class="form-group">
                <label for="description">Apraksts:</label>
                <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Saglabāt</button>
            <button type="button" class="btn btn-secondary" onclick="closeFormModal()">Atcelt</button>
        </form>
    </div>
</div>

</div>

{{-- Pagination Links --}}
<div class="d-flex justify-content-center mt-4">
    {{ $books->appends(request()->query())->links() }}
</div>
@endsection

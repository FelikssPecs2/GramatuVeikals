@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Grāmatas</h1>

        {{-- Rādīt "Pievienot grāmatu" pogu tikai administratoriem --}}
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="#" class="btn btn-primary" onclick="showCreateForm()">Pievienot grāmatu</a>
        @endif

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
                                <a href="#" class="btn btn-warning" onclick="showEditForm('{{ $book->id }}', '{{ $book->title }}', '{{ $book->author_id }}', '{{ $book->price }}', '{{ $book->age }}', '{{ $book->pages }}', '{{ $book->description }}')">Rediģēt</a> {{-- Include price in edit form --}}
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
                document.getElementById('bookForm').style.display = 'block';
                document.getElementById('formTitle').innerText = 'Izveidot grāmatu';
                document.getElementById('bookFormElement').action = '{{ route('books.store') }}';
                document.getElementById('formMethod').value = 'POST';
                document.getElementById('title').value = '';
                document.getElementById('price').value = ''; // Clear price field
                document.getElementById('age').value = ''; // Clear age field
                document.getElementById('pages').value = ''; // Clear pages field
                document.getElementById('description').value = ''; // Clear description field
                // Reset genre selection
                $('#genres').val(null).trigger('change');
            }

            function showEditForm(id, title, authorId, price, age, pages, description) {
                document.getElementById('bookForm').style.display = 'block';
                document.getElementById('formTitle').innerText = 'Rediģēt grāmatu';
                document.getElementById('bookFormElement').action = '/books/' + id;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('title').value = title;
                document.getElementById('author').value = authorId;
                document.getElementById('price').value = price; // Populate price field
                document.getElementById('age').value = age ? age : ''; // Populate age field
                document.getElementById('pages').value = pages ? pages : ''; // Populate pages field
                document.getElementById('description').value = description ? description : ''; // Populate description field
            }

            function hideForm() {
                document.getElementById('bookForm').style.display = 'none';
            }

            // Initialize Select2 for genres
            $(document).ready(function() {
                $('#genres').select2({
                    placeholder: "Izvēlieties žanrus"
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
        <div id="bookForm" class="mt-4" style="display: none;">
            <h2 id="formTitle">Izveidot grāmatu</h2>

            <form id="bookFormElement" action="{{ route('books.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

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
                    <label for="genres">Žanri:</label>
                    <select name="genre_ids[]" id="genres" class="form-control" multiple required>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}">{{ $genre->name }}</option>
                        @endforeach
                    </select>
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
                <button type="button" class="btn btn-secondary" onclick="hideForm()">Atcelt</button>
            </form>
        </div>
    </div>
@endsection

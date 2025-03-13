@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Pārdošanas</h1>

        <table class="table mt-4 table-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2">Grāmatas nosaukums</th>
                    <th class="px-4 py-2">Datums</th>
                    <th class="px-4 py-2" style="width: 60px;">Daudzums</th>
                    <th class="px-4 py-2">Cena</th> <th class="px-4 py-2">Kopā</th> @if(auth()->check() && auth()->user()->isAdmin())
                    <th class="px-4 py-2">Darbības</th>
                    @endif
                </tr>
            </thead>
            <tbody>
    @foreach($sales as $sale)   
        <tr>
            <td class="px-4 py-2">{{ $sale->book->title }}</td>
            <td class="px-4 py-2">{{ $sale->sale_date }}</td>
            <td class="px-4 py-2 text-right">{{ $sale->total_quantity }}</td>
            <td class="px-4 py-2">{{ $sale->book->price }}</td>
            <td class="px-4 py-2">{{ $sale->total_quantity * $sale->book->price }}</td>
            @if(auth()->check() && auth()->user()->isAdmin())
                <td class="px-4 py-2">
<form action="{{ route('sales.destroy') }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="book_id" value="{{ $sale->book_id }}">
    <input type="hidden" name="sale_date" value="{{ $sale->sale_date }}">
    <button type="submit" class="btn btn-danger">Dzēst</button>
</form>
                </td>
            @endif
        </tr>
    @endforeach
    <script>
        function showEditForm(saleId, bookId, saleDate, quantity) {
            document.getElementById('editSaleForm').style.display = 'block';
            document.getElementById('sale_id').value = saleId;
            document.getElementById('edit_book_id').value = bookId;
            document.getElementById('edit_sale_date').value = saleDate;
            document.getElementById('edit_quantity').value = quantity;

            // Update the form action with the sale ID
            document.getElementById('editForm').action = '/sales/' + saleId;
        }

        function hideEditForm() {
            document.getElementById('editSaleForm').style.display = 'none';
        }
    </script>
@endsection
@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h1>Pārdošanas</h1>

        <!-- Add the Download Button here -->
        <a href="{{ route('sales.export') }}" class="btn btn-primary mb-4">Lejupielādēt Pārdošanas Excel</a>

        <table class="table mt-4 table-auto">
    <thead>
        <tr>
            <th class="px-4 py-2">Grāmatas nosaukums</th>
            <th class="px-4 py-2">Datums</th>
            <th class="px-4 py-2" style="width: 60px;">Daudzums</th>
            <th class="px-4 py-2">Cena</th>
            <th class="px-4 py-2">Kopā</th>

            @if(auth()->check() && auth()->user()->isAdmin())
                <th class="px-4 py-2">Darbības</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach($salesGrouped as $groupKey => $salesGroup)
            @php
                $bookId = explode('-', $groupKey)[0];  // Extract book_id from the group key
                $saleDate = explode('|', $groupKey)[1];  // Extract full sale_date
                $totalQuantity = $salesGroup->sum('quantity');  // Calculate total quantity
                $book = $salesGroup->first()->book;  // Get the first book from the group (they're all the same)
            @endphp

            <tr>
                <td class="px-4 py-2">{{ $book->title }}</td>
                <td class="px-4 py-2">{{ $saleDate }}</td>
                <td class="px-4 py-2 text-right">{{ $totalQuantity }}</td>
                <td class="px-4 py-2">{{ $book->price }}</td>
                <td class="px-4 py-2">{{ $totalQuantity * $book->price }}</td>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <td class="px-4 py-2">
                        <form action="{{ route('sales.destroy', $salesGroup->first()->id) }}" method="POST" style="display:inline;">
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

    </div>
@endsection
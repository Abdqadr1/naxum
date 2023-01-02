@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3 mt-1">Top 100 Distributors</h3>
    <table class="table table-hover table-striped border table-hover my-2">
        <thead>
            <tr class="table-primary">
                <th scope="col">Top</th>
                <th scope="col">Distributor Name</th>
                <th scope="col">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributors as $d)
                <tr>
                    <td>{{$d->rank}}</td>
                    <td>{{$d->first_name." ".$d->last_name}}</td>
                    <td>${{$d->total_sales}}</td>
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="9">No distributor found.</td>
                </tr>
            @endforelse ()
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $distributors->onEachSide(3)->links() }}
    </div>
</div>
@endsection

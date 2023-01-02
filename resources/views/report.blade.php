@extends('layouts.app')

@section('content')
<div class="container">
    <form method="get" action="{{route('report')}}">
        @csrf
        <div class="row justify-content-around">
            <div class="col-8">
                <div class="mb-3 row col">
                    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold">Distributor</label>
                    <div class="col-sm-7 position-relative">
                        <input type="text" name="name" class="form-control" id="distributor" value="{{ request('name', '')}}" 
                            placeholder="Search by ID, Username, First Name, Last Name" autocomplete="no" required>
                            <div class="autocom" id="autocom">

                            </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3 col">
                    <label for="staticEmail" class="col-sm-9 col-form-label fw-bold">TOTAL COMMISSION: {{ sprintf("%0.2f", $total_commission ?: 0); }}
                    </label>
                </div>
            </div>
        </div>
        <div class="row justify-content-around">
            <div class="col-8">
                <div class="mb-3 d-flex">
                    <label for="staticEmail" class="col-sm-2 col-form-label fw-bold">Date From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', '') }}">
                    <label for="staticEmail" class="col-form-label fw-bold"> To </label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', '') }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
            <div class="col-4 row">
                <label for="staticEmail" class="col-sm-3 col-form-label ps-0">Search: </label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="staticEmail">
                </div>
            </div>
        </div>
    </form>
    <table class="table table-hover table-striped border table-hover my-2">
        <thead>
            <tr class="table-primary">
                <th scope="col">Invoice</th>
                <th scope="col">Purchaser</th>
                <th scope="col">Distributor</th>
                <th scope="col">Reffered Distributors</th>
                <th scope="col">Order Date</th>
                <th scope="col">Order Total</th>
                <th scope="col">Percentage</th>
                <th scope="col">Commission</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            @isset($orders)
                    @forelse ($orders as $order)
                    <tr>
                        <td>{{$order->invoice_number}}</td>
                        <td>{{$order->purchaser->full_name}}</td>
                        <td>{{
                            (optional($order->purchaser->referredBy)->is_distributor) ? 
                            $order->purchaser->referredBy->full_name : ""
                        }}</td>
                        <td>
                            {{
                                (optional($order->purchaser->referredBy)->is_distributor) ? 
                                $order->ref_count : 0
                            }}
                        </td>
                        <td>{{$order->date}}</td>
                        <td>{{ $order->order_total }}</td>
                        <td>{{ $order->percentage }}</td>
                        <td>{{ $order->commission }}</td>
                        <td>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#{{$order->invoice_number}}">
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="9">No order found.</td>
                    </tr>
                @endforelse
            @endisset
            
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        @isset($orders)
            {{ $orders->onEachSide(3)->links() }}
        @endisset
    </div>

    @isset($orders)
        @foreach ($orders as $order)
            <!-- Modal -->
            <div class="modal fade" id="{{$order->invoice_number}}" tabindex="-1" aria-labelledby="{{$order->invoice_number}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h1 class="modal-title fs-5" id="exampleModalLabel"><b>Invoice: </b> {{$order->invoice_number}}</h1>
                                <table class="table table-hover table-striped border table-hover my-2">
                                <thead>
                                    <tr class="table-primary">
                                        <th scope="col">SKU</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>{{$item->product->sku}}</td>
                                            <td>{{$item->product->name}}</td>
                                            <td>{{$item->product->price}}</td>
                                            <td>{{$item->quantity}}</td>
                                            <td>${{$item->total}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach        
    @endisset
</div>
<script src="js/autocomplete.js"></script>
@endsection

@extends('front.layouts.app')

@section('content')
    <section class="container">
        {{-- <div class="col-md-12 text-center py-5">
            @if (Session::has('success'))   
                <div class="alert alert-success">
                    {{ Session::get('success') }}
                </div>
            @endif
            
            <h1>Thank You!</h1>
            <p>Your Order Id is: {{ $id }}</p>
        </div> --}}
        <div class="col-md-6 offset-md-3 py-4">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card">
                        <div class="card-body">
                            <img src="{{ asset('front-assets/images/cart/bag.png') }}" class="san" alt="">
                        </div>
                        @if (Session::has('success'))   
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        <div class="card-body" style="background-color: #8ECDDD">
                            <h1 class="card-title">Thank You!</h5>
                            <p>Your Order Id is: {{ $id }}</p>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-6">
                    <div class="card">
                        @if (Session::has('success'))   
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">Thank You!</h5>
                            <p>Your Order Id is: {{ $id }}</p>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>

    <style>
        .san{
            width: auto;
            height: 150px !important;
            margin-left: 22% !important;
        }
    </style>
@endsection
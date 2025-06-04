@extends('layout.tenant')

@section('title', __('Inventory'))

@section('contents')
    <div id="app">
       <product-inventory></product-inventory>
    </div>
@endsection
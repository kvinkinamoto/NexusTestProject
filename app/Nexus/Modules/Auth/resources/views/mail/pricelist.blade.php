@extends('Auth::mail.layout.mail')

@section('title', __('emails.pricelist.subject', ['date' => now()->format('d-m-Y')]))

@section('content')
    {!! __('Auth::Auth.pricelist.content', ['name' => $Auth->name, 'url' => $url, 'date' => now()->format('d-m-Y')]) !!}
@endsection

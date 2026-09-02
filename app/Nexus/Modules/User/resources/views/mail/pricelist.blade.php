@extends('user::mail.layout.mail')

@section('title', __('emails.pricelist.subject', ['date' => now()->format('d-m-Y')]))

@section('content')
    {!! __('user::user.pricelist.content', ['name' => $user->name, 'url' => $url, 'date' => now()->format('d-m-Y')]) !!}
@endsection

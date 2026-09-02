@extends('Auth::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    {!! __('Auth::Auth.password_forgot.content', ['name' => $Auth->name, 'url' => $url]) !!}
@endsection

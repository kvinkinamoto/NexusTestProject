@extends('Auth::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    {!! __('Auth::Auth.activate_whosaler.content', ['name' => $Auth->name]) !!}
@endsection

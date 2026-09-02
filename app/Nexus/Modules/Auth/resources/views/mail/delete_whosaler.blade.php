@extends('Auth::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    {!! __('Auth::Auth.delete_whosaler.content', ['name' => $Auth->name]) !!}
@endsection

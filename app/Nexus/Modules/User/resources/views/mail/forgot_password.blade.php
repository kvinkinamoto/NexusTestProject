@extends('user::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    {!! __('user::user.password_forgot.content', ['name' => $user->name, 'url' => $url]) !!}
@endsection

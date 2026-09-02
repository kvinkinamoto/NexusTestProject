@extends('user::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    {!! __('user::user.delete_whosaler.content', ['name' => $user->name]) !!}
@endsection

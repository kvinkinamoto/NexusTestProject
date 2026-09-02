@extends('user::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    <h2>{{ __('user::user.mail_confirmation.confirm_button') }}</h2>
    <p>{{ __('user::user.mail_confirmation.greeting', ['name' => $user->name]) }}</p>
    <p>{!! __('user::user.mail_confirmation.thanks') !!}</p>
    <p>{{ __('user::user.mail_confirmation.confirm_text') }}</p>
    <p><a href="{{ $url }}" class="button">{{ __('user::user.mail_confirmation.confirm_button') }}</a></p>
    <p><strong>{{ __('user::user.mail_confirmation.note') }}</strong></p>
    <ul>
        <li>{!! __('user::user.mail_confirmation.validity') !!}</li>
        <li>{{ __('user::user.mail_confirmation.repeat') }}</li>
    </ul>
    <p>{{ __('user::user.mail_confirmation.ignore') }}</p>
    <p>{{ __('user::user.mail_confirmation.support') }} <a href="mailto:support@oiltime.com">support@oiltime.com</a></p>
    <p>{!! __('user::user.mail_confirmation.team') !!}</p>
@endsection

@extends('Auth::mail.layout.mail')

@section('title', __('emails.confirm_button'))

@section('content')
    <h2>{{ __('Auth::Auth.mail_confirmation.confirm_button') }}</h2>
    <p>{{ __('Auth::Auth.mail_confirmation.greeting', ['name' => $Auth->name]) }}</p>
    <p>{!! __('Auth::Auth.mail_confirmation.thanks') !!}</p>
    <p>{{ __('Auth::Auth.mail_confirmation.confirm_text') }}</p>
    <p><a href="{{ $url }}" class="button">{{ __('Auth::Auth.mail_confirmation.confirm_button') }}</a></p>
    <p><strong>{{ __('Auth::Auth.mail_confirmation.note') }}</strong></p>
    <ul>
        <li>{!! __('Auth::Auth.mail_confirmation.validity') !!}</li>
        <li>{{ __('Auth::Auth.mail_confirmation.repeat') }}</li>
    </ul>
    <p>{{ __('Auth::Auth.mail_confirmation.ignore') }}</p>
    <p>{{ __('Auth::Auth.mail_confirmation.support') }} <a href="mailto:support@oiltime.com">support@oiltime.com</a></p>
    <p>{!! __('Auth::Auth.mail_confirmation.team') !!}</p>
@endsection

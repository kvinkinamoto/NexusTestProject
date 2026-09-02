@extends('frontend::layouts.base')
@section('content')
    <div class="container">
        <div class="row ">
            <div class="col-md-6">
    <form method="POST" action="{{ route('register') }}" class="register-form">
        @csrf

        <!-- Name -->
        <div>
            <p>
            <x-input-label for="name" :value="__('Name')" />
                <span class="control">
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </span>
            </p>
        </div>

        <!-- Email Address -->
        <div>
            <p>
            <label for="email">@lang('user::user.email')</label>
                <span class="control">
            <input id="email" class="block mt-1 w-full" type="email"
                   name="email" value="{{old('email')}}" required autofocus autocomplete="username"/>
                </span>
            </p>
            @if ($errors->get('email'))
                <ul>
                    @foreach ((array) $errors->get('email') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Password -->
        <div class="">
            <p>
            <label for="password">@lang('user::user.password')</label>
                <span class="control">
            <input id="password" class=""
                   type="password"
                   name="password"
                   required autocomplete="current-password"/>
                </span>
            </p>
            @if ($errors->get('password'))
                <ul>
                    @foreach ((array) $errors->get('password') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Confirm Password -->
        <div class="">
            <p>
            <label for="password_confirmation">@lang('user::user.password_confirmation')</label>
                <span class="control">
            <input id="password_confirmation" class=""
                   type="password"
                   name="password_confirmation"
                   required autocomplete="new-password"/>
                </span>
            </p>
            @if ($errors->get('password_confirmation'))
                <ul>
                    @foreach ((array) $errors->get('password_confirmation') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <p class="text-right">
            <input type="submit" class="ml-4" type="submit" value="{{ __('Register') }}" />
            </p>
        </div>
    </form>
            </div>
            </div>
            </div>

@endsection

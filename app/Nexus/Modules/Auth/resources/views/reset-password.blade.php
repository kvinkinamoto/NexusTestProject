@extends('siteFrontend::layouts.layout')

@section('content')
    <div class="container-fluid page-header py-5">
        <h1 class="text-center text-white display-6">
            {{ __('auth::translate.change_password') }}
        </h1>
    </div>

    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="bg-white p-4 rounded shadow-sm">
                        <reset-password-form
                            token="{{ $token }}"
                            email="{{ $email }}"
                            login-href="{{ route('shop.home') }}"
                        ></reset-password-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head dark">
            <div class="auth-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke member area Anda</p>
        </div>

        <div class="auth-body">
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill alert-icon"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="alert-x">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    <button type="button" class="alert-x">&times;</button>
                </div>
            @endif

            <form method="POST" action="{{ route('member.login.submit') }}" novalidate>
                @csrf

                <div class="fg">
                    <label for="email">Email</label>
                    <div class="fi-wrap">
                        <i class="bi bi-envelope fi-icon"></i>
                        <input type="email" name="email" id="email" class="fi @error('email') is-invalid @enderror"
                               placeholder="contoh@email.com" value="{{ old('email') }}" autofocus required>
                    </div>
                    @error('email')
                        <div class="fi-err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="fg">
                    <label for="password">Password</label>
                    <div class="fi-wrap">
                        <i class="bi bi-lock fi-icon"></i>
                        <input type="password" name="password" id="password" class="fi @error('password') is-invalid @enderror"
                               placeholder="Masukkan password" required>
                        <button type="button" class="pw-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    @error('password')
                        <div class="fi-err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <label class="remember-check">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Ingat saya
                </label>

                <button type="submit" class="btn-dark">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>
        </div>

        <div class="auth-foot">
            Belum punya akun? <a href="{{ route('member.register') }}">Daftar sekarang</a>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head gradient">
            <div class="auth-icon"><i class="bi bi-person-plus-fill"></i></div>
            <h2>Buat Akun Baru</h2>
            <p>Daftar untuk mengakses member area</p>
        </div>

        <div class="auth-body">
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

            <form method="POST" action="{{ route('member.register.submit') }}" novalidate>
                @csrf

                <div class="fg">
                    <label for="name">Nama Lengkap</label>
                    <div class="fi-wrap">
                        <i class="bi bi-person fi-icon"></i>
                        <input type="text" name="name" id="name" class="fi @error('name') is-invalid @enderror"
                               placeholder="Masukkan nama lengkap" value="{{ old('name') }}" autofocus required>
                    </div>
                    @error('name')
                        <div class="fi-err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="fg">
                    <label for="email">Email</label>
                    <div class="fi-wrap">
                        <i class="bi bi-envelope fi-icon"></i>
                        <input type="email" name="email" id="email" class="fi @error('email') is-invalid @enderror"
                               placeholder="contoh@email.com" value="{{ old('email') }}" required>
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
                               placeholder="Minimal 8 karakter" required>
                        <button type="button" class="pw-btn"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="pw-str">
                        <div class="pw-bar"><div class="pw-fill"></div></div>
                        <div class="pw-txt"></div>
                    </div>
                    @error('password')
                        <div class="fi-err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="fg">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="fi-wrap">
                        <i class="bi bi-lock-fill fi-icon"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="fi"
                               placeholder="Ulangi password" required>
                        <button type="button" class="pw-btn"><i class="bi bi-eye"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-person-plus"></i> Daftar Sekarang
                </button>
            </form>
        </div>

        <div class="auth-foot">
            Sudah punya akun? <a href="{{ route('member.login') }}">Login di sini</a>
        </div>
    </div>
</div>
@endsection

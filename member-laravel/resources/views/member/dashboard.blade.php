@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dash">
    <div class="wrap">
        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom:1.5rem">
                <i class="bi bi-check-circle-fill alert-icon"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="alert-x">&times;</button>
            </div>
        @endif

        <div class="welcome">
            <div class="welcome-av">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div>
                <h2>Halo, {{ Auth::user()->name }}! 👋</h2>
                <p>Selamat datang kembali di member area Anda</p>
            </div>
        </div>

        <div class="dash-grid">
            <div>
                <div class="d-card" style="margin-bottom:1.5rem">
                    <div class="d-card-head"><i class="bi bi-person-badge"></i> Informasi Akun</div>
                    <div class="d-card-body">
                        <div class="info-row">
                            <span class="info-lbl"><i class="bi bi-person"></i> Nama</span>
                            <span class="info-val">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl"><i class="bi bi-envelope"></i> Email</span>
                            <span class="info-val">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl"><i class="bi bi-shield-check"></i> Role</span>
                            <span class="info-val"><span class="badge-role"><i class="bi bi-star-fill"></i> {{ ucfirst(Auth::user()->role) }}</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl"><i class="bi bi-calendar3"></i> Terdaftar</span>
                            <span class="info-val">{{ Auth::user()->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if(Auth::user()->email_verified_at)
                        <div class="info-row">
                            <span class="info-lbl"><i class="bi bi-patch-check"></i> Verifikasi</span>
                            <span class="info-val" style="color:var(--success)"><i class="bi bi-check-circle-fill"></i> Terverifikasi</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <div class="d-card">
                    <div class="d-card-head"><i class="bi bi-lightning-charge"></i> Aksi Cepat</div>
                    <div class="d-card-body">
                        <div class="action-list">
                            <a href="{{ url('/') }}" class="btn-ol"><i class="bi bi-folder2-open"></i> File Manager</a>
                            <form method="POST" action="{{ route('member.logout') }}">
                                @csrf
                                <button type="submit" class="btn-ol-red" style="width:100%"><i class="bi bi-box-arrow-right"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

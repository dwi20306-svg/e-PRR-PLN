@php $isAdmin = auth()->user()->isAdmin(); @endphp
@extends($isAdmin ? 'layouts.admin' : 'layouts.ulp')
@section('title', 'Kelola Profil')

@section('content')
<!-- Wrapper Utama -->
<div style="max-width: 620px; margin: 0 auto; padding: 10px 0;">

    <!-- Heading Halaman -->
    <div class="page-heading" style="text-align: center; margin-bottom: 24px;">
        <h1>Kelola Profil</h1>
        <p>Ubah informasi akun Anda</p>
    </div>

    <!-- Container Card -->
    <div style="display: grid; gap: 20px;">

        {{-- ── Profil ── --}}
        <div class="card">
            <div class="card__header">
                <div class="card__title">Informasi Akun</div>
            </div>
            <div class="card__body">
                @if($errors->updateProfile->any())
                    <div class="alert alert-error">{{ $errors->updateProfile->first() }}</div>
                @endif

                <form method="POST" action="{{ route('profil.update') }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username"
                               value="{{ old('username', $user->username) }}" required>
                    </div>
                    @if($user->isUlp())
                    <div class="form-group">
                        <label>ULP</label>
                        <input type="text" value="{{ $user->ulp_label }}" disabled
                               style="background:#f5f5f5;color:#999">
                    </div>
                    @endif
                    
                    <!-- Tombol Aksi Akhir (Ditambahkan Tombol Kembali) -->
                    <div class="flex gap-2 mt-4" style="justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">💾 Simpan Profil</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Ganti Password ── --}}
        <div class="card">
            <div class="card__header">
                <div class="card__title">Ubah Password</div>
            </div>
            <div class="card__body">
                @if($errors->updatePassword->any())
                    <div class="alert alert-error">{{ $errors->updatePassword->first() }}</div>
                @endif

                <form method="POST" action="{{ route('profil.password') }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                    
                    <!-- Tombol Aksi Akhir (Ditambahkan Tombol Kembali) -->
                    <div class="flex gap-2 mt-4" style="justify-content: flex-end;">
                        <a href="{{ url()->previous() }}" class="btn btn-outline">⬅️ Kembali</a>
                        <button type="submit" class="btn btn-primary">🔒 Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
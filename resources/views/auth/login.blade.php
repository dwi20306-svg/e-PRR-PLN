<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — e-PRR UP3 Banda Aceh</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">

        {{-- Logo --}}
        <div class="login-header">

            <img src="{{ asset('images/logo_pln.png') }}"
                alt="Logo PLN"
                class="login-logo">

            <h1 class="login-heading">e-PRR</h1>

            <p class="login-subtitle">Sistem Piutang Ragu-Ragu</p>
            <p class="login-subtitle">PLN UP3 Banda Aceh</p>

        </div>

            {{-- Alert error --}}
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username"
                       value="{{ old('username') }}"
                       placeholder="Masukkan username"
                       required autofocus
                       style="padding:10px 12px">
            </div>

            <div class="form-group">
                <label>Password</label>
                <div style="position:relative">
                    <input type="password" name="password" id="passwordInput"
                           placeholder="Masukkan password"
                           required
                           style="padding:10px 40px 10px 12px;width:100%">
                    {{-- Toggle show/hide password --}}
                    <button type="button" onclick="togglePassword()"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;cursor:pointer;font-size:16px;color:#6B7A99">
                        👁
                    </button>
                </div>
            </div>

            <div class="form-group" style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" name="remember" id="rem" style="width:auto;cursor:pointer">
                <label for="rem" style="margin:0;font-size:13px;color:#555;cursor:pointer">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full"
                    style="justify-content:center;padding:12px;font-size:14px;margin-top:4px">
                Masuk
            </button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:11px;color:#9CA3AF">
            PLN UP3 Banda Aceh &copy; {{ date('Y') }}
        </p>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>e-PRR — @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

{{-- ══ NAVBAR ══ --}}
<nav class="navbar">
    <div class="navbar__brand">
        <img src="{{ asset('images/logo-pln.png') }}" alt="PLN"
             onerror="this.style.display='none'">
        <div class="navbar__brand-text">
            <span>e-PRR</span>
            <span class="sub">{{ auth()->user()->ulp_label }}</span>
        </div>
    </div>
    <div class="navbar__spacer"></div>

    <div class="navbar__user">
        <div class="navbar__avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div>
            <div class="navbar__user-name">{{ auth()->user()->name }}</div>
        </div>
        <div class="navbar__dropdown">
            <a href="{{ route('profil.edit') }}">
                <i class="fa-solid fa-gear"></i> Kelola Profil 
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>   
            </form>
        </div>
    </div>
</nav>

{{-- ══ KONTEN (tanpa sidebar) ══ --}}
<main class="main--no-sidebar">
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

@stack('modals')
@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });
});
function openModal(id)  { document.getElementById(id).classList.add('open');    }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
</script>
</body>
</html>

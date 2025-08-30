<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Kairat's Last Stand")</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/game.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #fff !important;
        }
        .btn-primary {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            border: none;
            color: #000;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #ffed4e, #ffd700);
            color: #000;
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('game.index') }}">
                ⚽ Kairat's Last Stand
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('game.index') }}">Game</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('leaderboard.index') }}">Leaderboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item">
                            <span class="nav-link">Welcome, {{ Auth::user()->name ?? Auth::user()->email }}!</span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/game.js') }}"></script>
    @yield('scripts')
</body>
</html>

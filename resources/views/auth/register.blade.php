@extends('layouts.app')

@section('title', 'Register - Kairat\'s Last Stand')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
            <div class="card-header text-center">
                <h3 class="text-white">⚽ Register</h3>
                <p class="text-white-50">Join Kairat's Last Stand!</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('auth.register') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-white">Name (Optional)</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" 
                               placeholder="Enter your name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-white">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="Enter your email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-white">Password *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Enter your password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label text-white">Confirm Password *</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" 
                               placeholder="Confirm your password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Register & Play</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="text-white-50">Already have an account? 
                        <a href="{{ route('auth.login') }}" class="text-white">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="card-title">{{ config('app.name', 'Laravel') }}</h1>
                    <p class="lead">Bienvenido al sitio. Inicia sesión para acceder al panel o regístrate si aún no tienes cuenta.</p>

                    <div class="mt-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">Ir al Dashboard</a>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Editar perfil</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary me-2">Iniciar sesión</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary">Registrarse</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

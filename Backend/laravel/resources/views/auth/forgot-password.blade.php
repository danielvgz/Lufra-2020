<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .login-page {
            background: #f4f6f9;
        }
        .login-logo {
            display: none;
        }
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .login-card-body {
            padding: 2rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }
        .login-header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .login-header .brand-text {
            color: #343a40;
            font-size: 1.25rem;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .login-header .company-info {
            color: #6c757d;
            font-size: 0.75rem;
            display: block;
            margin-bottom: 2px;
        }
        .login-box-msg {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .btn-primary {
            background: #007bff;
            border: none;
            padding: 0.6rem 1rem;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card">
        <div class="card-body login-card-body">
            <div class="login-header">
                @if(config('settings.image'))
                    <img src="{{ asset('storage/settings/') }}/{{ config('settings.image') }}" alt="Logo">
                @endif
                <span class="brand-text">
                    {{ config('settings.app_name', config('app.name', 'Sistema de Nóminas')) }}
                </span>
                @if(config('settings.register_number'))
                    <small class="company-info"><i class="fas fa-id-card mr-1"></i>{{ config('settings.register_number') }}</small>
                @endif
                @if(config('settings.app_email'))
                    <small class="company-info"><i class="fas fa-envelope mr-1"></i>{{ config('settings.app_email') }}</small>
                @endif
            </div>
            
            <p class="login-box-msg text-center">
                <i class="fas fa-key mr-2"></i>¿Olvidaste tu contraseña?<br>
                <small>Ingresa tu correo y te enviaremos un enlace para recuperarla.</small>
            </p>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <ul class="mb-0 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="small text-muted">Correo Electrónico</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Enviar Enlace de Recuperación
                </button>
            </form>

            <hr class="my-3">

            <p class="text-center mb-0 small">
                <a href="{{ route('login') }}" class="font-weight-bold"><i class="fas fa-arrow-left mr-1"></i>Volver al inicio de sesión</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>

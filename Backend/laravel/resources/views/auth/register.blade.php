<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}"><b>Laravel</b> App</a>
    </div>
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Crea tu cuenta</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                <div class="input-group mb-1">
                    <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Nombre" required autofocus />
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                </div>
                @error('name')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="input-group mb-1">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email" required />
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                </div>
                @error('email')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="input-group mb-1">
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Contraseña" required />
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                @error('password')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="input-group mb-1">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Confirmar contraseña" required />
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                @error('password_confirmation')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <div class="row">
                    <div class="col-8">
                        <p class="mb-1">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>

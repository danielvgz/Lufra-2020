<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Dashboard' }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

</head>
<body>
<nav class="navbar navbar-expand navbar-dark bg-dark">
  <a class="navbar-brand" href="{{ route('home') }}">Gestión Nóminas</a>
  <ul class="navbar-nav ml-auto">
    @auth
      <li class="nav-item"><a href="{{ route('perfil') }}" class="nav-link">{{ auth()->user()->name }}</a></li>
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
          <button type="submit" class="btn btn-sm btn-outline-danger">Cerrar sesión</button>
        </form>
      </li>
    @endauth
  </ul>
</nav>
<div class="container-fluid">
  <div class="row">
    <aside class="col-md-3 col-lg-2 bg-light border-right min-vh-100 pt-3">
      <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action" href="{{ route('home') }}">Inicio</a>
        @php
          $role = auth()->user()->role ?? null;
          if (!$role && auth()->check()) {
            $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
              ->join('roles','roles.id','=','rol_usuario.rol_id')
              ->where('rol_usuario.user_id', auth()->id())
              ->value('roles.nombre');
          }
        @endphp
        @if($role === 'administrador')
          <a class="list-group-item list-group-item-action" href="{{ route('departamentos.view') }}">Departamentos</a>
          <a class="list-group-item list-group-item-action" href="{{ route('empleados.index') }}">Empleados</a>
          <a class="list-group-item list-group-item-action" href="{{ route('contratos.index') }}">Contratos</a>
          <a class="list-group-item list-group-item-action" href="{{ route('nominas.index') }}">Períodos de Nómina</a>
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">Recibos y Pagos</a>
                            @if(auth()->check() && auth()->user()->puede('asignar_roles'))
                           
                                <a class="list-group-item list-group-item-action" href="{{ url('/roles') }}">
                                   Roles
                                </a>
                           
                            @endif
                            @if(auth()->check() && auth()->user()->puede('asignar_roles'))
                                <a class="list-group-item list-group-item-action" href="{{ url('/permissions') }}" >Permisos</a>
                            @endif
                              <a class="list-group-item list-group-item-action"href="{{ url('/empresa/perfil') }}" class="nav-link">Configuración</a>
                         
          @elseif($role === 'empleado')
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">Recibos y Pagos</a>
        @endif
      </div>
    </aside>
    <main class="col-md-9 col-lg-10 py-4">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('status') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if($errors && $errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>

@extends('layouts')
@section('content')
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-file-signature mr-1"></i> Detalle del contrato</h3>
        <div>
          @php
            $currentUser = auth()->user();
            $isOwner = $currentUser && isset($contrato->empleado_user_id) && $contrato->empleado_user_id == $currentUser->id;
          @endphp
          @if($isOwner)
            <a href="javascript:history.back()" class="btn btn-sm btn-secondary">Ir atrás</a>
          @else
            <a href="{{ route('contratos.index') }}" class="btn btn-sm btn-secondary">Volver a contratos</a>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($contrato)
          <div class="row">
            <div class="col-md-6">
              <h5>Empleado</h5>
              <p><strong>Código:</strong> {{ $contrato->empleado_codigo ?? '-' }}</p>
              <p><strong>Cédula:</strong> {{ $contrato->empleado_cedula ?? '-' }}</p>
              <p><strong>Nombre:</strong> {{ $contrato->empleado_name ?? $contrato->empleado_email ?? '-' }}</p>
            </div>
            <div class="col-md-6 text-right">
              <h5>Estado</h5>
              <p><span class="badge bg-{{ ($contrato->estado==='activo') ? 'success' : (($contrato->estado==='terminado' || $contrato->estado==='suspendido') ? 'danger' : 'secondary') }}">{{ ucfirst($contrato->estado ?? '—') }}</span></p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-6">
              <p><strong>Puesto:</strong> {{ $contrato->puesto ?? '-' }}</p>
              <p><strong>Tipo:</strong> {{ $contrato->tipo_contrato ? \Illuminate\Support\Str::of($contrato->tipo_contrato)->replace('_',' ')->title() : '-' }}</p>
              <p><strong>Frecuencia:</strong> {{ $contrato->frecuencia_pago ?? '-' }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Fecha inicio:</strong> {{ $contrato->fecha_inicio ?? '-' }}</p>
              <p><strong>Fecha fin:</strong> {{ $contrato->fecha_fin ?? 'Indefinida' }}</p>
              <p><strong>Salario base:</strong> {{ isset($contrato->salario_base) ? number_format($contrato->salario_base,2) : '-' }}</p>
            </div>
          </div>
          <hr>
          <div class="d-flex justify-content-end">
            @php
              $user = auth()->user();
              $isAdmin = $user && ($user->tieneRol('administrador') || $user->tieneRol('Administrador'));
            @endphp
            @if($isAdmin)
              <a href="{{ route('contratos.edit', ['id'=>$contrato->id]) }}" class="btn btn-primary mr-2">Editar</a>
              <form method="POST" action="{{ route('contratos.destroy', ['id'=>$contrato->id]) }}" onsubmit="return confirm('¿Eliminar contrato #{{ $contrato->id }}?')">
                @csrf
                <button class="btn btn-outline-danger">Eliminar</button>
              </form>
            @endif
          </div>
        @else
          <p>No se encontró el contrato.</p>
        @endif
      </div>
    </div>
  </div>
@endsection

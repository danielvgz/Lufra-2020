@extends('layouts')
@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0">Monedas</h3>
      <a href="{{ route('recibos_pagos') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>
    <div class="card-body">
      <?php $items = \Illuminate\Support\Facades\DB::table('monedas')->orderBy('nombre')->limit(200)->get(); ?>
      <form method="POST" action="{{ route('monedas.crear') }}" class="form-inline mb-3">@csrf
        <input type="text" name="nombre" class="form-control form-control-sm mr-2" placeholder="Nombre (ej: Dólar)" required>
        <input type="text" name="codigo" class="form-control form-control-sm mr-2" placeholder="Código (ej: USD)" maxlength="3" required>
        <input type="text" name="simbolo" class="form-control form-control-sm mr-2" placeholder="Símbolo (ej: $)" maxlength="10">
        <button class="btn btn-sm btn-primary">Agregar</button>
      </form>
      @if(count($items))
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Nombre</th><th>Código</th><th>Símbolo</th><th>Acciones</th></tr></thead>
            <tbody>
            @foreach($items as $it)
              <tr>
                <td>
                  <form method="POST" action="{{ route('monedas.editar') }}" class="form-inline mb-0">@csrf
                    <input type="hidden" name="id" value="{{ $it->id }}">
                    <input type="text" name="nombre" value="{{ $it->nombre }}" class="form-control form-control-sm mr-2" required>
                    <input type="text" name="codigo" value="{{ $it->codigo }}" class="form-control form-control-sm mr-2" maxlength="3" required>
                    <input type="text" name="simbolo" value="{{ $it->simbolo }}" class="form-control form-control-sm mr-2" maxlength="10">
                    <button class="btn btn-sm btn-success">Guardar</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p>No hay monedas.</p>
      @endif
    </div>
  </div>
</div>
@endsection

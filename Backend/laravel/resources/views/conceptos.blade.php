@extends('layouts')
@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0">Conceptos de pago</h3>
      <a href="{{ route('recibos_pagos') }}" class="btn btn-sm btn-outline-secondary">Volver</a>
    </div>
    <div class="card-body">

      <form method="POST" action="{{ route('conceptos.crear') }}" class="form-inline mb-3">@csrf
        <input type="text" name="nombre" class="form-control form-control-sm mr-2" placeholder="Nuevo concepto" required>
        <button class="btn btn-sm btn-primary">Agregar</button>
      </form>
      @if(count($items))
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Concepto</th><th>Acciones</th></tr></thead>
            <tbody>
            @foreach($items as $it)
              <tr>
                <td>
                  <form method="POST" action="{{ route('conceptos.editar') }}" class="form-inline mb-0">@csrf
                    <input type="hidden" name="id" value="{{ $it->id }}">
                    <input type="text" name="nombre" value="{{ $it->nombre }}" class="form-control form-control-sm mr-2" required>
                    <button class="btn btn-sm btn-success">Guardar</button>
                  </form>
                </td>
                <td>
                  <form method="POST" action="{{ route('conceptos.eliminar') }}" onsubmit="return confirm('Eliminar concepto?')">@csrf
                    <input type="hidden" name="id" value="{{ $it->id }}">
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          {{ $items->links() }}
        </div>
      @else
        <p>No hay conceptos.</p>
      @endif
    </div>
  </div>
</div>
@endsection

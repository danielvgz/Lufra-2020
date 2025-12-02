@extends('layouts')
@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-plus mr-1"></i> Nuevo departamento</h5></div>
        <div class="card-body">
          <form method="POST" action="{{ route('departamentos.nuevo') }}">
            @csrf
            <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
            <div class="form-group"><label>Código</label><input name="code" class="form-control" required></div>
            <div class="form-group"><label>Descripción</label><input name="description" class="form-control"></div>
            <button class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> Guardar</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-sitemap mr-1"></i> Departamentos</h5>
          <form method="GET" action="{{ route('departamentos.view') }}" class="form-inline">
            <input name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Buscar nombre o código">
            <button class="btn btn-outline-secondary btn-sm">Buscar</button>
          </form>
        </div>
        <div class="card-body">
          <?php
            $q = request('q');
            $query = \Illuminate\Support\Facades\DB::table('departamentos as d')->select('d.id','d.nombre','d.codigo')->orderBy('d.nombre');
            if ($q) { $query->where(function($x) use($q){ $x->where('d.nombre','like',"%$q%")->orWhere('d.codigo','like',"%$q%"); }); }
            $deps = $query->limit(200)->get();
          ?>
          @if(count($deps))
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Nombre</th><th>Código</th><th>Acciones</th></tr></thead>
                <tbody>
                  @foreach($deps as $d)
                    <tr>
                      <td>{{ $d->nombre }}</td>
                      <td>{{ $d->codigo }}</td>
                      <td>
                        <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-{{ $d->id }}').classList.toggle('d-none')">Editar</button>
                        <form method="POST" action="{{ route('departamentos.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar departamento?')">
                          @csrf
                          <input type="hidden" name="id" value="{{ $d->id }}">
                          <button class="btn btn-xs btn-danger">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                    <tr id="edit-{{ $d->id }}" class="d-none"><td colspan="3">
                      <form method="POST" action="{{ route('departamentos.editar') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $d->id }}">
                        <div class="form-row">
                          <div class="form-group col-md-6"><label>Nombre</label><input name="name" class="form-control form-control-sm" value="{{ $d->nombre }}" required></div>
                          <div class="form-group col-md-4"><label>Código</label><input name="code" class="form-control form-control-sm" value="{{ $d->codigo }}" required></div>
                        </div>
                        <div class="form-group"><label>Descripción</label><input name="description" class="form-control form-control-sm" value="{{ \Illuminate\Support\Facades\DB::table('departamentos')->where('id',$d->id)->value('descripcion') }}"></div>
                        <button class="btn btn-success btn-sm">Guardar</button>
                      </form>
                    </td></tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="mb-0">No hay departamentos.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

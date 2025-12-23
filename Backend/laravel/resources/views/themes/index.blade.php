@extends('layouts')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Gestión de Temas</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('themes.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Subir tema (.zip)</label>
                    <input type="file" name="zip" class="form-control-file">
                </div>
                <div class="form-group mt-2">
                    <button class="btn btn-primary">Instalar tema</button>
                </div>
            </form>

            <hr>
            <h5>Temas instalados</h5>
            <table class="table table-sm">
                <thead><tr><th>Nombre</th><th>Slug</th><th>Instalado</th><th>Activo</th><th>Acciones</th></tr></thead>
                <tbody>
                @foreach($themes as $t)
                    <tr>
                        <td>{{ $t->name }}</td>
                        <td>{{ $t->slug }}</td>
                        <td>
                            @if(isset($t->installed_at) && $t->installed_at)
                                @if(method_exists($t,'installed_at') || is_object($t->installed_at))
                                    {{ is_object($t->installed_at) ? $t->installed_at->format('Y-m-d H:i') : (is_string($t->installed_at) ? $t->installed_at : '—') }}
                                @else
                                    {{ is_string($t->installed_at) ? $t->installed_at : '—' }}
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>@if(isset($t->active) && $t->active)<span class="badge badge-success">Activo</span>@endif</td>
                        <td>
                            @if(isset($t->id))
                                @if(!$t->active)
                                    <form method="POST" action="{{ route('themes.activate',['theme'=>$t->id]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Activar</button></form>
                                    <form method="POST" action="{{ route('themes.delete',['theme'=>$t->id]) }}" class="d-inline" onsubmit="return confirm('Eliminar tema?')">@csrf<button class="btn btn-xs btn-danger">Eliminar</button></form>
                                @else
                                    <form method="POST" action="{{ route('themes.deactivate',['theme'=>$t->id]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-warning">Desactivar</button></form>
                                    <form method="POST" action="{{ route('themes.delete',['theme'=>$t->id]) }}" class="d-inline" onsubmit="return confirm('Eliminar tema?')">@csrf<button class="btn btn-xs btn-danger">Eliminar</button></form>
                                @endif
                            @else
                                {{-- Theme present in filesystem but not registered in DB --}}
                                <form method="POST" action="{{ route('themes.register',['slug'=>$t->slug]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-outline-primary">Registrar</button></form>
                                <form method="POST" action="{{ route('themes.remove',['slug'=>$t->slug]) }}" class="d-inline ml-1" onsubmit="return confirm('Eliminar carpeta del tema {{ $t->slug }}?')">@csrf<button class="btn btn-xs btn-outline-danger">Eliminar carpeta</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

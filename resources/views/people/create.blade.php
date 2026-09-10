@extends('layouts.app')

@section('title', 'Nuevo empleado · SJ-SIG')

@section('content')
<article class="card person-sheet">
    <p class="kicker">Alta unitaria</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Nuevo empleado</h2>
    <p class="muted">Los mismos campos de la plantilla Excel. HV, certificados y afiliaciones PDF se cargan después en Documentos.</p>
    <form method="post" action="{{ route('people.store') }}" class="form-grid" style="margin-top:16px" enctype="multipart/form-data">
        @csrf
        @include('people._form')
        <div class="span-2" style="display:flex;gap:8px;margin-top:8px">
            <button class="btn" type="submit">Crear y abrir ficha</button>
            <a class="btn ghost" href="{{ route('people.index') }}">Cancelar</a>
        </div>
    </form>
</article>
@endsection

@extends('layouts.app')

@section('title', 'Editar '.$person->full_name.' · SJ-SIG')

@section('content')
<article class="card person-sheet">
    <p class="kicker">{{ $person->document_type }} {{ $person->document_number }}</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">Editar ficha</h2>
    <p class="muted">Los mismos campos de la plantilla Excel. Los PDF no se tocan aquí.</p>
    <form method="post" action="{{ route('people.update', $person) }}" class="form-grid" style="margin-top:16px" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('people._form', ['person' => $person])
        <div class="span-2" style="display:flex;gap:8px;margin-top:8px">
            <button class="btn" type="submit">Guardar ficha</button>
            <a class="btn ghost" href="{{ route('people.show', $person) }}">Cancelar</a>
        </div>
    </form>
</article>
@endsection

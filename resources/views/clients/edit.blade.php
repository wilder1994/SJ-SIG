@extends('layouts.app')

@section('title', 'Editar cliente · SJ-SIG')

@section('content')
<article class="card" style="max-width:820px">
    <p class="kicker">Universo</p>
    <h2 class="display" style="font-size:26px;margin:4px 0 8px">{{ $client->name }}</h2>
    <form method="post" action="{{ route('clients.update', $client) }}" class="form-grid" style="margin-top:16px">
        @csrf
        @method('PUT')
        @include('clients._form', ['client' => $client])
        @if($errors->any())
            <p class="span-2" style="color:var(--bad)">{{ $errors->first() }}</p>
        @endif
        <div class="span-2"><button class="btn" type="submit">Guardar</button></div>
    </form>
</article>
@endsection

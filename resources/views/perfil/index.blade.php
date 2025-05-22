@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Meu Perfil</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>Nome:</strong> {{ $usuario->name }}</p>
            <p><strong>Email:</strong> {{ $usuario->email }}</p>
            <p><strong>Cadastrado em:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <a href="{{ route('home') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection

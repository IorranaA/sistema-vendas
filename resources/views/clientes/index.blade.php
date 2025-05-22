@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Clientes</h2>

    <a href="{{ route('clientes.create') }}" class="btn btn-success mb-3">Novo Cliente</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th><th>Email</th><th>Telefone</th><th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nome }}</td>
                    <td>{{ $cliente->email }}</td>
                    <td>{{ $cliente->telefone }}</td>
                    <td>
    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-primary">Editar</a>

    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline-block">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este cliente?')">Apagar</button>
    </form>

    <a href="{{ route('clientes.vendas', $cliente->id) }}" class="btn btn-sm btn-secondary">Ver Vendas</a>
</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

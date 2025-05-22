@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Vendas de {{ $cliente->nome }}</h2>

    <a href="{{ route('clientes.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    @if ($cliente->vendas->isEmpty())
        <p>Este cliente ainda não possui vendas.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th><th>Data</th><th>Total</th><th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cliente->vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                        <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

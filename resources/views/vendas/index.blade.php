@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Lista de Vendas</h2>

    <a href="{{ route('vendas.create') }}" class="btn btn-success mb-3">Nova Venda</a>

    @if ($vendas->count() > 0)
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Valor Total</th>
                    <th>Forma de Pagamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendas as $venda)
                <tr>
                    <td>{{ $venda->id }}</td>
                    <td>{{ $venda->cliente ? $venda->cliente->nome : '---' }}</td>
                    <td>{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>{{ ucfirst($venda->forma_pagamento) }}</td>
                    <td class="d-flex gap-2">
                        <a href="{{ route('vendas.show', $venda->id) }}" class="btn btn-sm btn-primary">Ver Detalhes</a>

                        <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" onsubmit="return confirmarExclusao();" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $vendas->links() }}
    @else
        <p>Nenhuma venda encontrada.</p>
    @endif
</div>

{{-- Script de confirmação --}}
<script>
    function confirmarExclusao() {
        return confirm('Tem certeza que deseja excluir esta venda? Esta ação não pode ser desfeita.');
    }
</script>
@endsection

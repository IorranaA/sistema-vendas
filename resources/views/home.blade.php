@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    {{-- KPIs --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total de Vendas</h5>
                    <h3 class="card-text">{{ $totalVendas }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total em Vendas</h5>
                    <h3 class="card-text">R$ {{ number_format($valorTotalVendas, 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Clientes</h5>
                    <h3 class="card-text">{{ $totalClientes }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Produtos</h5>
                    <h3 class="card-text">{{ $totalProdutos }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas Vendas --}}
    <div class="card shadow-sm">
        <div class="card-header">Últimas Vendas</div>
        <div class="card-body p-0">
            <table class="table table-hover m-0">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Valor Total</th>
                        <th>Forma de Pagamento</th>
                        <th>Parcelas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasVendas as $venda)
                        <tr>
                            <td>{{ $venda->cliente->nome ?? 'N/A' }}</td>
                            <td>{{ $venda->created_at->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                            <td>{{ ucfirst($venda->forma_pagamento) }}</td>
                            <td>{{ $venda->parcelas()->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Nenhuma venda registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

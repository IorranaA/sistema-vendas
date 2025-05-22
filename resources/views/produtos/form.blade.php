@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ isset($produto) ? 'Editar Produto' : 'Novo Produto' }}</h2>

    <form action="{{ isset($produto) ? route('produtos.update', $produto->id) : route('produtos.store') }}" method="POST">
        @csrf
        @if(isset($produto)) @method('PUT') @endif

        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ $produto->nome ?? old('nome') }}" required>
        </div>

        <div class="mb-3">
            <label>Preço</label>
            <input type="number" step="0.01" name="preco" class="form-control" value="{{ $produto->preco ?? old('preco') }}" required>
        </div>

        <div class="mb-3">
            <label>Descrição</label>
            <textarea name="descricao" class="form-control">{{ $produto->descricao ?? old('descricao') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection

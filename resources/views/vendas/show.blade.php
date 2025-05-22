@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes da Venda #{{ $venda->id }}</h2>

    <div class="mb-3">
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">Informações da Venda</div>
        <div class="card-body">
            <p><strong>Cliente:</strong> {{ $venda->cliente->nome }}</p>
            <p><strong>Data:</strong> {{ $venda->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Forma de Pagamento:</strong> {{ ucfirst($venda->forma_pagamento) }}</p>
            <p><strong>Valor Total:</strong> R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Produtos</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venda->itens as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Parcelas</span>
        <button class="btn btn-sm btn-warning" id="btnEditarParcelas">Editar Parcelas</button>
    </div>
    <div class="card-body">
        @if ($venda->parcelas->count() > 0)
            <form id="formEditarParcelas" method="POST" action="{{ route('vendas.parcelas.atualizar', $venda->id) }}">
                @csrf
                <table class="table" id="tabelaParcelas">
                    <thead>
                        <tr>
                            <th>Data de Vencimento</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venda->parcelas as $i => $parcela)
                            <tr>
                                <td>
                                    <span class="texto">{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</span>
                                    <input type="date" name="parcelas[{{ $i }}][data_vencimento]" class="form-control d-none" value="{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('Y-m-d') }}">
                                    <input type="hidden" name="parcelas[{{ $i }}][id]" value="{{ $parcela->id }}">
                                </td>
                                <td>
                                    <span class="texto">R$ {{ number_format($parcela->valor, 2, ',', '.') }}</span>
                                    <input type="number" step="0.01" name="parcelas[{{ $i }}][valor]" class="form-control d-none" value="{{ $parcela->valor }}">
                                </td>
                               <td>
    <span class="texto">{{ $parcela->paga ? 'Paga' : 'Pendente' }}</span>
    <button type="button" class="btn btn-sm btn-danger btnRemoverParcela d-none">Remover</button>
</td>

                                
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="my-3 d-none" id="containerBtnAdicionarParcela">
    <button type="button" class="btn btn-sm btn-primary" id="btnAdicionarParcela">Adicionar Parcela</button>
</div>


<div id="intervaloContainer" class="d-none">
    <label for="intervaloDias" class="form-label mt-3">Intervalo entre parcelas (em dias)</label>
    <input type="number" class="form-control w-25" id="intervaloDias" name="intervaloDias" min="1" value="30" placeholder="Intervalo (dias)">
</div>

                <button type="submit" class="btn btn-success d-none" id="btnSalvarParcelas">Salvar Alterações</button>
                <button type="button" class="btn btn-secondary d-none" id="btnCancelarParcelas">Cancelar</button>
            </form>
        @else
            <p>Nenhuma parcela registrada.</p>
        @endif
    </div>
</div>

</div>
@push('scripts')
<script>
document.getElementById('btnEditarParcelas').addEventListener('click', function () {
    // Habilita inputs
    document.querySelectorAll('#tabelaParcelas .texto').forEach(el => el.classList.add('d-none'));
    document.querySelectorAll('#tabelaParcelas input').forEach(el => el.classList.remove('d-none'));

    // Mostra botões
    document.getElementById('btnSalvarParcelas').classList.remove('d-none');
    document.getElementById('btnCancelarParcelas').classList.remove('d-none');
    document.getElementById('containerBtnAdicionarParcela').classList.remove('d-none');

    document.getElementById('intervaloContainer').classList.remove('d-none');
    document.querySelectorAll('.btnRemoverParcela').forEach(btn => btn.classList.remove('d-none'));

    
    this.classList.add('d-none');
    frozenIndexes.clear();
});



document.getElementById('btnCancelarParcelas').addEventListener('click', function () {
    window.location.reload();
});
</script>
@endpush
@push('scripts')
<script>
let totalOriginal = {{ $venda->valor_total }};
let frozenIndexes = new Set();

function atualizarEventos() {
    inputsValor = document.querySelectorAll('#tabelaParcelas input[name$="[valor]"]');
    inputsData = document.querySelectorAll('#tabelaParcelas input[name$="[data_vencimento]"]');

    inputsValor.forEach((input, index) => {
        input.addEventListener('input', () => validarERecalcular(index));
    });

    inputsData.forEach((input, index) => {
        input.addEventListener('change', () => atualizarDatas(index));
    });

    //validarERecalcular();
}

function validarERecalcular(triggerIndex = null) {
    let somaCongeladas = 0;
    let valores = [];
    let datas = [];

    document.querySelectorAll('#tabelaParcelas tbody tr').forEach((row, i) => {
        let valor = parseFloat(row.querySelector('input[name$="[valor]"]').value || 0);
        let data = row.querySelector('input[name$="[data_vencimento]"]').value;
        valores.push(valor);
        datas.push(data);
        if (i === triggerIndex) frozenIndexes.add(i);
    });

    // Redistribuição automática
    let livres = [];
    frozenIndexes.forEach(i => somaCongeladas += valores[i]);
    valores.forEach((_, i) => { if (!frozenIndexes.has(i)) livres.push(i); });

    let restante = totalOriginal - somaCongeladas;
    if (livres.length && restante >= 0) {
        let media = restante / livres.length;
        livres.forEach(i => {
            valores[i] = parseFloat(media.toFixed(2));
            document.querySelectorAll('#tabelaParcelas tbody tr')[i].querySelector('input[name$="[valor]"]').value = media.toFixed(2);
        });
    }

    // Validação acumulada
    let acumulado = 0;
    let valido = true;
    document.querySelectorAll('#tabelaParcelas tbody tr').forEach((row, i) => {
        acumulado += valores[i];
        let campo = row.querySelector('input[name$="[valor]"]');
        if (acumulado > totalOriginal + 0.001) {
            campo.classList.add('is-invalid');
            valido = false;
        } else {
            campo.classList.remove('is-invalid');
        }
    });

    // Desabilita botão se der erro
    document.getElementById('btnSalvarParcelas').disabled = !valido;
    if (!valido && !document.getElementById('alertParcela')) {
        let alert = document.createElement('div');
        alert.className = 'alert alert-danger mt-3';
        alert.id = 'alertParcela';
        alert.innerText = 'Erro: O valor das parcelas excede o total da venda.';
        document.getElementById('formEditarParcelas').appendChild(alert);
    } else if (valido && document.getElementById('alertParcela')) {
        document.getElementById('alertParcela').remove();
    }
}

function atualizarDatas(index) {
    let baseDate = new Date(inputsData[index].value);
    let intervalo = parseInt(document.getElementById('intervaloDias').value || 30);

    for (let i = index + 1; i < inputsData.length; i++) {
        baseDate.setDate(baseDate.getDate() + intervalo);
        inputsData[i].value = baseDate.toISOString().split('T')[0];
    }
}

// Adicionar nova parcela
document.getElementById('btnAdicionarParcela').addEventListener('click', () => {
    const tbody = document.querySelector('#tabelaParcelas tbody');
    const index = tbody.children.length;

    const novaLinha = document.createElement('tr');
    const hoje = new Date().toISOString().split('T')[0];

    novaLinha.innerHTML = `
        <td>
            <input type="date" name="parcelas[${index}][data_vencimento]" class="form-control" value="${hoje}">
        </td>
        <td>
            <input type="number" step="0.01" name="parcelas[${index}][valor]" class="form-control" value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger btnRemoverParcela">Remover</button>
        </td>
    `;
    tbody.appendChild(novaLinha);
    atualizarEventos();
    redistribuirParcelas();
});


// Remover parcela
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btnRemoverParcela')) {
        const row = e.target.closest('tr');
        const index = Array.from(row.parentNode.children).indexOf(row);
        row.remove();
        frozenIndexes.delete(index);
        redistribuirParcelas();
    }
});
function redistribuirParcelas() {
    let valores = [];
    let rows = document.querySelectorAll('#tabelaParcelas tbody tr');

    rows.forEach((row, i) => {
        let input = row.querySelector('input[name$="[valor]"]');
        valores.push(parseFloat(input.value) || 0);
    });

  
    rows.forEach((row, i) => {
        row.querySelectorAll('input').forEach(input => {
            if (input.name.includes('[data_vencimento]')) {
                input.name = `parcelas[${i}][data_vencimento]`;
            }
            if (input.name.includes('[valor]')) {
                input.name = `parcelas[${i}][valor]`;
            }
            if (input.name.includes('[id]')) {
                input.name = `parcelas[${i}][id]`;
            }
        });
    });

    // Recalcular
    let somaCongeladas = 0;
    let livres = [];

    valores.forEach((v, i) => {
        if (frozenIndexes.has(i)) {
            somaCongeladas += v;
        } else {
            livres.push(i);
        }
    });

    let restante = totalOriginal - somaCongeladas;
    let valorRedistribuido = (livres.length > 0) ? parseFloat((restante / livres.length).toFixed(2)) : 0;

    livres.forEach(i => {
        document.querySelectorAll('#tabelaParcelas tbody tr')[i].querySelector('input[name$="[valor]"]').value = valorRedistribuido.toFixed(2);
    });

    validarERecalcular();
}


// Inicial
atualizarEventos();
</script>
@endpush


@endsection

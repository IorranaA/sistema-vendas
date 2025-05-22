@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container">
    <h2>Nova Venda</h2>

    <form action="{{ route('vendas.store') }}" method="POST" id="form-venda">
        @csrf

        {{-- CLIENTE --}}
        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
            <select name="cliente_id" class="form-select" required>
                <option value="">-- Selecione --</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                @endforeach
            </select>
        </div>

        {{-- PRODUTOS --}}
        <div class="mb-3">
            <label class="form-label">Itens da Venda</label>
            <table class="table table-bordered" id="tabela-itens">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-secondary" id="add-item">Adicionar Produto</button>
        </div>

        {{-- FORMA DE PAGAMENTO --}}
        <div class="mb-3">
            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
            <select name="forma_pagamento" class="form-select" required>
                <option value="cartao">Cartão</option>
                <option value="boleto">Boleto</option>
                <option value="dinheiro">Dinheiro</option>
            </select>
        </div>

        {{-- Parcelar? --}}
<div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="checkbox-parcelar">
    <label class="form-check-label" for="checkbox-parcelar">Parcelar?</label>
</div>

      {{-- PARCELAS --}}
<div class="mb-3" id="bloco-parcelas" style="display: none;">
    <label class="form-label">Parcelas</label>
    <div class="d-flex mb-2">
        <input type="number" id="qtd_parcelas" class="form-control me-2" placeholder="Qtd de parcelas" min="1">
        <input type="number" id="intervalo_dias" class="form-control me-2" placeholder="Intervalo (dias)" min="1" value="30">
        <button type="button" class="btn btn-primary" id="gerar-parcelas">Gerar Parcelas</button>
    </div>

    <table class="table table-bordered" id="tabela-parcelas">
    <thead>
        <tr>
            <th>Data de Vencimento</th>
            <th>Valor da Parcela</th>
            <th></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<button type="button" class="btn btn-outline-primary mt-2" id="adicionar-parcela" style="display: none;">Adicionar Parcela</button>

</div>


        {{-- VALOR TOTAL --}}
        <div class="mb-3">
            <label class="form-label">Valor Total</label>
            <input type="text" name="valor_total" id="valor_total" class="form-control" readonly required>
        </div>

        <button type="submit" class="btn btn-success">Salvar Venda</button>
    </form>
</div>

{{-- PRODUTOS DISPONÍVEIS--}}
<script>
    const produtos = @json($produtos);
</script>

{{-- paerte 2 scripts--}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let itemIndex = 0;

function atualizarTotal() {
    let total = 0;
    $('#tabela-itens tbody tr').each(function() {
        const subtotal = parseFloat($(this).find('.subtotal').val()) || 0;
        total += subtotal;
    });



    $('#valor_total').val(total.toFixed(2));
}

function adicionarItem() {
    let row = $(`
        <tr>
            <td>
                <select name="produtos[${itemIndex}][produto_id]" class="form-select produto-select" required>
                    <option value="">Selecione</option>
                    ${produtos.map(prod => `<option value="${prod.id}" data-preco="${prod.preco}">${prod.nome}</option>`).join('')}
                </select>
            </td>
            <td><input type="number" name="produtos[${itemIndex}][quantidade]" class="form-control quantidade" value="1" min="1" required></td>
            <td><input type="number" step="0.01" name="produtos[${itemIndex}][preco_unitario]" class="form-control preco_unitario" required></td>
            <td><input type="text" name="produtos[${itemIndex}][subtotal]" class="form-control subtotal" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm remover-item">X</button></td>
        </tr>
    `);

    $('#tabela-itens tbody').append(row);

    row.find('.produto-select').select2({
        width: '100%',
        placeholder: 'Selecione um produto',
        allowClear: true
    });

    itemIndex++;
    atualizarTotal();
}

function gerarParcelas() {
    const qtd = parseInt($('#qtd_parcelas').val()) || 0;
    const intervalo = parseInt($('#intervalo_dias').val()) || 30;
    const total = parseFloat($('#valor_total').val()) || 0;

    if (qtd < 1 || total <= 0) return;

    const valorParcela = (total / qtd).toFixed(2);
    const hoje = new Date();

    $('#tabela-parcelas tbody').html('');
    for (let i = 0; i < qtd; i++) {
        let vencimento = new Date(hoje);
        vencimento.setDate(vencimento.getDate() + (intervalo * i));
        let dataFormatada = vencimento.toISOString().split('T')[0];

        $('#tabela-parcelas tbody').append(`
            <tr data-index="${i}" data-personalizada="false">
                <td>
                    <input type="date" name="parcelas[${i}][data_vencimento]" class="form-control data-vencimento" value="${dataFormatada}" required>

                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="number" name="parcelas[${i}][valor]" class="form-control valor-parcela" step="0.01" value="${valorParcela}" readonly required>
                        <input type="hidden" name="parcelas[${i}][personalizada]" value="false">
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 btn-personalizar">🛠️</button>
                    </div>
                </td>
                <td><button type="button" class="btn btn-danger btn-sm remover-parcela">X</button></td>
            </tr>
        `);
    }
    

}

$('#adicionar-parcela').show();
$(document).ready(function() {
    $('#add-item').on('click', adicionarItem);
    $('#adicionar-parcela').on('click', adicionarParcelaManual);


    // Aplica Select2 quando produto for adicionado
    $(document).on('change', '.data-vencimento', function () {
    const row = $(this).closest('tr');
    const index = row.index();
    const novaData = new Date($(this).val());
    const intervalo = parseInt($('#intervalo_dias').val()) || 30;

    const rows = $('#tabela-parcelas tbody tr');

    // Atualiza as datas das próximas parcelas
    for (let i = index + 1; i < rows.length; i++) {
        novaData.setDate(novaData.getDate() + intervalo);
        const dataFormatada = novaData.toISOString().split('T')[0];
        $(rows[i]).find('.data-vencimento').val(dataFormatada);
    }
});


    // Preenche automaticamente o preço unitário ao selecionar o produto
    $(document).on('change', '.produto-select', function() {
        const row = $(this).closest('tr');
        const produto = $(this).find('option:selected');
        const preco = parseFloat(produto.data('preco')) || 0;
        const quantidade = parseInt(row.find('.quantidade').val()) || 1;

        row.find('.preco_unitario').val(preco.toFixed(2));
        row.find('.subtotal').val((preco * quantidade).toFixed(2));
        atualizarTotal();
    });

    // Atualiza o subtotal quando mudar a quantidade ou o preço manualmente
    $(document).on('input', '.preco_unitario, .quantidade', function () {
        const row = $(this).closest('tr');
        const preco = parseFloat(row.find('.preco_unitario').val()) || 0;
        const quantidade = parseInt(row.find('.quantidade').val()) || 1;

        row.find('.subtotal').val((preco * quantidade).toFixed(2));
        atualizarTotal();
            $('#form-venda').on('submit', function(e) {
        const valorTotal = parseFloat($('#valor_total').val()) || 0;
        let somaParcelas = 0;

        $('.valor-parcela').each(function() {
            somaParcelas += parseFloat($(this).val()) || 0;
        });

        // Arredondar para evitar problemas com casas decimais (n funciona ainda)
        // somaParcelas = parseFloat(somaParcelas.toFixed(2));

        if (somaParcelas > valorTotal) {
            e.preventDefault();
            alert(`A soma das parcelas (${somaParcelas.toFixed(2)}) excede o valor total da venda (${valorTotal.toFixed(2)}). Corrija antes de salvar.`);
            
            // Destacar campos dps de erro em calclo
            $('.valor-parcela').each(function () {
                $(this).toggleClass('is-invalid', true);
            });

            return false;
        }
    });

    });

    $(document).on('click', '.remover-item', function() {
        $(this).closest('tr').remove();
        atualizarTotal();
    });

    $('#gerar-parcelas').on('click', gerarParcelas);

    $(document).on('click', '.remover-parcela', function() {
    const row = $(this).closest('tr');
    const valorRemovido = parseFloat(row.find('input[name$="[valor]"]').val()) || 0;

    row.remove();

    redistribuirValorRemovido(valorRemovido);
});

    $('#checkbox-parcelar').on('change', function() {
        if ($(this).is(':checked')) {
            $('#bloco-parcelas').show();
        } else {
            $('#bloco-parcelas').hide();
            $('#tabela-parcelas tbody').html('');
            $('#qtd_parcelas').val('');
        }
    });
        // dps de editar uma parcela manualmente
   $(document).on('input', '.valor-parcela', function () {
    const input = $(this);
    const row = input.closest('tr');
    const index = row.index();

    const valorDigitado = parseFloat(input.val()) || 0;
    const total = parseFloat($('#valor_total').val()) || 0;

    if (valorDigitado > total) {
        input.addClass('is-invalid');
    } else {
        input.removeClass('is-invalid');
        recalcularParcelasAoEditar(index);
    }
});

    // Ativa o modo de personalização para uma parcela
$(document).on('click', '.btn-personalizar', function () {
    const row = $(this).closest('tr');
    row.attr('data-personalizada', 'true');
    row.find('.valor-parcela').prop('readonly', false);
    row.find('input[name$="[personalizada]"]').val('true');
});


});
function recalcularParcelasAoEditar(indexEditado) {
    const total = parseFloat($('#valor_total').val()) || 0;
    const rows = $('#tabela-parcelas tbody tr');

    let somaFixas = 0;
    let parcelasLivres = [];

    // Passo 1: Separar parcelas fixas (personalizadas) e livres
    rows.each(function(i) {
        const personalizada = $(this).attr('data-personalizada') === 'true';
        const valor = parseFloat($(this).find('input[name$="[valor]"]').val()) || 0;

        if (personalizada || i === indexEditado) {
            somaFixas += valor;
        } else {
            parcelasLivres.push($(this));
        }
    });

    // Passo 2: Calcular o restante do valor para distribuir
    const restante = total - somaFixas;
    const valorUnitario = (restante / parcelasLivres.length).toFixed(2);

    // Passo 3: Atualizar somente parcelas não personalizadas e não a editada
    parcelasLivres.forEach(row => {
        row.find('input[name$="[valor]"]').val(valorUnitario);
    });
}
function redistribuirValorRemovido(valorRemovido) {
    const rows = $('#tabela-parcelas tbody tr');
    const parcelasLivres = [];

    let somaTotal = 0;

    rows.each(function() {
        const personalizada = $(this).attr('data-personalizada') === 'true';
        const inputValor = $(this).find('input[name$="[valor]"]');
        const valorAtual = parseFloat(inputValor.val()) || 0;

        if (!personalizada) {
            parcelasLivres.push(inputValor);
        }

        somaTotal += valorAtual;
    });

    // Verifica se há parcelas livres para redistribuir
    if (parcelasLivres.length === 0) return;

    const extraPorParcela = (valorRemovido / parcelasLivres.length);

    parcelasLivres.forEach(input => {
        const atual = parseFloat(input.val()) || 0;
        input.val((atual + extraPorParcela).toFixed(2));
    });
}
function adicionarParcelaManual() {
    const rows = $('#tabela-parcelas tbody tr');
    const intervalo = parseInt($('#intervalo_dias').val()) || 30;

    let ultimaData = new Date();

    // Descobrir a última data entre as parcelas existentes
    rows.each(function() {
        const data = new Date($(this).find('.data-vencimento').val());
        if (data > ultimaData) ultimaData = data;
    });

    ultimaData.setDate(ultimaData.getDate() + intervalo);
    const dataFormatada = ultimaData.toISOString().split('T')[0];
    const index = rows.length;

    $('#tabela-parcelas tbody').append(`
        <tr data-index="${index}" data-personalizada="false">
            <td>
                <input type="date" name="parcelas[${index}][data_vencimento]" class="form-control data-vencimento" value="${dataFormatada}" required>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="number" name="parcelas[${index}][valor]" class="form-control valor-parcela" step="0.01" value="0.00" readonly required>
                    <input type="hidden" name="parcelas[${index}][personalizada]" value="false">
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2 btn-personalizar">🛠️</button>
                </div>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remover-parcela">X</button></td>
        </tr>
    `);

    recalcularParcelasAoEditar(-1);
}




</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@endsection

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\Parcela;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    public function index()
    {
        $vendas = Venda::with('cliente')->orderBy('created_at', 'desc')->paginate(10);
        return view('vendas.index', compact('vendas'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $produtos = Produto::all();
        return view('vendas.create', compact('clientes', 'produtos'));
    }

    public function store(Request $request)
{
    $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'forma_pagamento' => 'required|string',
        'valor_total' => 'required|numeric|min:0.01',
        'produtos' => 'required|array|min:1',
        'produtos.*.produto_id' => 'required|exists:produtos,id',
        'produtos.*.quantidade' => 'required|integer|min:1',
        'produtos.*.preco_unitario' => 'required|numeric|min:0.01',
        'parcelas' => 'nullable|array',
        'parcelas.*.data_vencimento' => 'required_with:parcelas|date',
        'parcelas.*.valor' => 'required_with:parcelas|numeric|min:0.01',
    ]);

    DB::beginTransaction();

    try {
        $venda = Venda::create([
            'user_id' => auth()->id(),
            'cliente_id' => $request->cliente_id,
            'forma_pagamento' => $request->forma_pagamento,
            'valor_total' => $request->valor_total,
        ]);

        foreach ($request->produtos as $produto) {
            $quantidade = $produto['quantidade'];
            $precoUnitario = $produto['preco_unitario'];
            $subtotal = $quantidade * $precoUnitario;

            ItemVenda::create([
                'venda_id' => $venda->id,
                'produto_id' => $produto['produto_id'],
                'quantidade' => $quantidade,
                'preco_unitario' => $precoUnitario,
                'subtotal' => $subtotal,
            ]);
        }

        if ($request->has('parcelas')) {
            foreach ($request->parcelas as $parcela) {
                Parcela::create([
                    'venda_id' => $venda->id,
                    'data_vencimento' => $parcela['data_vencimento'],
                    'valor' => $parcela['valor'],
                    'paga' => false,
                ]);
            }
        }

        DB::commit();

        return redirect()->route('vendas.index')->with('success', 'Venda registrada com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Erro ao salvar venda: ' . $e->getMessage());
    }
}


    public function show(string $id)
{
    $venda = Venda::with(['cliente', 'itens.produto', 'parcelas'])->findOrFail($id);
    return view('vendas.show', compact('venda'));
}

    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy($id)
{
    $venda = Venda::findOrFail($id);
    $venda->delete();

    return redirect()->route('vendas.index')->with('success', 'Venda excluída com sucesso.');
}

    public function updateParcelas(Request $request, $id)
{
    $request->validate([
        'parcelas' => 'nullable|array',
        'parcelas.*.data_vencimento' => 'required|date',
        'parcelas.*.valor' => 'required|numeric|min:0.01',
    ]);

    DB::beginTransaction();

    try {
        $venda = Venda::findOrFail($id);
        $idsRecebidos = collect($request->parcelas)->pluck('id')->filter()->toArray();

        // Apagar parcelas removidas
        Parcela::where('venda_id', $id)
            ->whereNotIn('id', $idsRecebidos)
            ->delete();

        // Atualizar ou criar parcelas
        foreach ($request->parcelas as $parcelaData) {
            if (isset($parcelaData['id'])) {
                $parcela = Parcela::findOrFail($parcelaData['id']);
                $parcela->update([
                    'data_vencimento' => $parcelaData['data_vencimento'],
                    'valor' => $parcelaData['valor'],
                ]);
            } else {
                Parcela::create([
                    'venda_id' => $venda->id,
                    'data_vencimento' => $parcelaData['data_vencimento'],
                    'valor' => $parcelaData['valor'],
                    'paga' => false,
                ]);
            }
        }

        DB::commit();
        return redirect()->route('vendas.show', $id)->with('success', 'Parcelas atualizadas com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Erro ao atualizar parcelas: ' . $e->getMessage());
    }
}


}

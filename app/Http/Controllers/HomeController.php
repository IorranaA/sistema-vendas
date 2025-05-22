<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
{
    $totalVendas = Venda::count();
    $valorTotalVendas = Venda::sum('valor_total');
    $totalClientes = Cliente::count();
    $totalProdutos = Produto::count();

    $ultimasVendas = Venda::with('cliente')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();

    return view('home', compact(
        'totalVendas',
        'valorTotalVendas',
        'totalClientes',
        'totalProdutos',
        'ultimasVendas'
    ));
}
}

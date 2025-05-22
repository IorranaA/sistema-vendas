<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendaController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\HomeController;


Auth::routes();

// Página inicial
Route::get('/', [HomeController::class, 'index'])->name('home');

// Rotas de Cliente
Route::resource('clientes', ClienteController::class)->except(['show']);

// Rotas de Produt
Route::resource('produtos', ProdutoController::class)->except(['show']);

// Rotas de Venda
Route::resource('vendas', VendaController::class)->except(['edit', 'update', 'destroy']);

Route::post('/vendas/{id}/atualizar-parcelas', [VendaController::class, 'updateParcelas'])->name('vendas.parcelas.atualizar');
Route::resource('vendas', VendaController::class);
Route::get('/clientes/{cliente}/vendas', [App\Http\Controllers\ClienteController::class, 'vendas'])->name('clientes.vendas');



Route::get('/perfil', function () {
    $usuario = Auth::user();
    return view('perfil.index', compact('usuario'));
})->name('perfil.index')->middleware('auth');


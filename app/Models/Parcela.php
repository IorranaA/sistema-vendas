<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $fillable = [
        'venda_id',
        'data_vencimento',
        'valor',
        'paga',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}


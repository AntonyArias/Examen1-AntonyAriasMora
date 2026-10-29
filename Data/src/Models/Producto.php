<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    public $timestamps = false;

    protected $fillable = [
        'sku',
        'nombre_producto',
        'descripcion',
        'precio_unitario',
        'stock_actual',
        'categoria',
    ];

    protected $casts = [
        'id' => 'integer',
        'stock_actual' => 'integer',
    ];
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemModel extends Model
{
    protected $table = 'sales.sale_item';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'sale_id',
        'product_id',
        'product_name',
        'category_name',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(SaleModel::class, 'sale_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class SaleModel extends Model
{
    protected $table = 'sales.sale';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'sold_at',
        'sold_by_user_id',
        'sold_by_username',
        'created_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItemModel::class, 'sale_id', 'id');
    }
}

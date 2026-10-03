<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModel extends Model
{
    use SoftDeletes;

    protected $table = 'sales.product';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'category_id',
        'price',
        'stock',
        'image_url',
        'deleted_at',
        'version',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'version' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id');
    }
}

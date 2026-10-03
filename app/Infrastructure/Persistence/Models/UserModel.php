<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'sales.user';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'username',
        'full_name',
        'role',
        'password_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

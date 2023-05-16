<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'total_cost' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'total_cost',
        "address_line_one",
        "address_line_two",
        "city",
        "state",
        "postal_code",
        "country"
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory; 
    
    // the "fillable" property defines which fields are used when adding/updating data
    protected $fillable = [
        'name',
        'slug',
        'description', 
        'image',
        'brand',
        'category',
        'price',
    ];

    // this changes the data type of "price" to float, which is understood by postgresql
    protected $casts = [
        'price' => 'float',
    ];

    public $timestamps = false;
    
}

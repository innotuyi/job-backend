<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'Product';

    protected $fillable = [
        'name',
        'categoryID',
        'description',
        'price',
        'photo1',
        'photo2',
        'photo3',
        'photo4'
    ];
}

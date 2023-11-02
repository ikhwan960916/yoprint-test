<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_key',
        'title',
        'description',
        'style',
        'sanmar_mainframe_size',
        'size',
        'color_name',
        'piece_price',
    ];
}

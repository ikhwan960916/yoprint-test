<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'file_path',
        'status',
        'uploaded_at',
    ];
}

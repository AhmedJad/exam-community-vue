<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManyImage extends Model
{
    protected $guarded=[];
    use HasFactory;
    protected $casts = [
        'images' => "json",
    ];
}

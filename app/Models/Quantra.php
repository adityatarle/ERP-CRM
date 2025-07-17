<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quantra extends Model
{
    use HasFactory;

    protected $fillable = ['amount', 'note'];
}

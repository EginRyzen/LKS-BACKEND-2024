<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    protected $hidden = [
        'allowed_domains',
        'created_at',
        'updated_at'
    ];
}

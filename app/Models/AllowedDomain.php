<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedDomain extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    protected $hidden = [
        'allowed_domains',
    ];
}

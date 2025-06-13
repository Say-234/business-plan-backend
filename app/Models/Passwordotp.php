<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passwordotp extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'email',
        'otp',
        'created_at',
    ];
}

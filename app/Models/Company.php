<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'is_active',
        'created_at'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
}

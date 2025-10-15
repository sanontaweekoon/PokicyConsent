<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyCategory extends Model
{
    use HasFactory;

    protected $table = 'policy_categories';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'name',
        'is_mandatory',
        'created_at'
    ];
    protected $casts = [
        'is_mandatory' => 'boolean',
        'create_at' => 'datetime'
    ];
}

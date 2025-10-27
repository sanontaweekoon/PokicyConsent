<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckLogin extends Model
{
    use HasFactory;

    protected $table = 'CheckLogin';
    public $timestamps = false;

    protected $primaryKey = 'EmpCode';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'Fname',
        'Lname',
        'IdentityCard',
        'BirthDate',
        'EmpLevelID',
        'EmpCode'
    ];

    protected $casts = [
        'BirthDate' => 'date:Y-m-d'
    ];
}

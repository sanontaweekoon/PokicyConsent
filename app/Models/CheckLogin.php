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
        'EmpCode',
        'Fname',
        'NickName',
        'EmplLevelCode',
        'AAD',
        'DisplayAD',
        'mailAD',
        'PositionName',
        'Department',
        'IdentityCard',
        'BirthDate'
    ];

    protected $casts = [
        'BirthDate' => 'date:Y-m-d'
    ];

    public static function getEmployeesWithEmail(){
        return static::query()
            ->select([
                'EmpCode',
                'Fname as full_name',
                'EmpLevelCode as level',
                'mailAD as email',
                'PositionName',
                'Department'
            ])
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->orderBy('Fname')
            ->get();
    }
}

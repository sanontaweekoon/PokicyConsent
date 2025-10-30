<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    // query email พนักงานที่มี email เท่านั้น
    public static function getEmployeesWithEmail()
    {
        return static::query()
            ->select([
                'EmpCode',
                'Fname as full_name',
                'EmplLevelCode as level',
                'mailAD as email',
                'PositionName',
                'Department'
            ])
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->orderBy('Fname')
            ->get();
    }

    // ค้นหา พนักงานที่มี email
    public static function searchEmployeesWithEmail($query)
    {
        if (empty($query)) {
            return collect([]);
        }

        return static::query()
            ->select([
                'EmpCode',
                'Fname as full_name',
                'mailAD as email',
                'Department',
                'PositionName'
            ])
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->where(function ($q) use ($query) {
                $q->where('mailAD', 'LIKE', "%{$query}%")
                    ->orWhere('Fname', 'LIKE', "%{$query}%")
                    ->orWhere('EmpCode', 'LIKE', "%{$query}%");
            })
            ->orderBy('Fname')
            ->limit(50)
            ->get();
    }

    // ดึงพนักงานตามแผนกที่มี email
    public static function getEmployeesByDepartment($department)
    {
        return static::query()
            ->select([
                'EmpCode',
                'Fname as full_name',
                'mailAD as email',
                'Department',
                'PositionName'
            ])
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->where('Department', $department)
            ->orderBy('Fname')
            ->get();
    }

    // ดึงรายชื่อแผนกทั้งหมดที่มี email
    public static function getDepartments()
    {
        return static::query()
            ->select('Department')
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->whereNotNull('Department')
            ->where('Department', '!=', '')
            ->groupBy('Department')
            ->orderBy('Department')
            ->pluck('Department');
    }

    public static function getEmployeeWithAD($identityCard, $birthDate)
    {
        return static::table('CheckLogin as cl')
            ->select([
                'cl.EmpCode',
                'cl.Fname as full_name',
                'cl.EmplLevelCode',
                'cl.PositionName',
                'cl.Department',
                'cl.IdentityCard',
                'cl.BirthDate',
                'cl.mailAD as Email',
                'cl.DisplayAD as DisplayName',
            ])
            ->where('cl.IdentityCard', $identityCard)
            ->whereDate('cl.BirthDate', $birthDate)
            ->whereNotNull('cl.mailAD')
            ->where('cl.mailAD', '!=', '')
            ->first();
    }

    public static function getByEmpCodeWithAD($empCode)
    {
        return DB::table('CheckLogin')
            ->select([
                'EmpCode',
                'Fname as full_name',
                'EmplLevelCode',
                'PositionName',
                'Department',
                'mailAD as Email',
                'DisplayAD as DisplayName'
            ])
            ->where('EmpCode', $empCode)
            ->whereNotNull('mailAD')
            ->where('mailAD', '!=', '')
            ->first();
    }
}

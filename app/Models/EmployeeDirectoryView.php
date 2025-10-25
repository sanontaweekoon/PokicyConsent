<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDirectoryView extends Model
{
    use HasFactory;

    protected $table = 'V_EmpAll';
    public $timestamps = false;
}

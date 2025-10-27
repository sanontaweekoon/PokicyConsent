<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyTargetResolved extends Model
{
    use HasFactory;

    protected $table = 'policy_target_resolved';
    public $timestamps = true;

    protected $fillable = [
        'policy_window_id',
        'employee_code',
        'reason',
        'locked',
        'uniqid'
    ];

    protected $casts = [
        'policy_window_id' => 'integer',
        'locked' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function window(): BelongsTo{
        return $this->belongsTo(PolicyWindow::class, 'policy_window_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(CheckLogin::class, 'employee_code', 'EmpCode');
    }
}

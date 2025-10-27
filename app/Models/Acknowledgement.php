<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acknowledgement extends Model
{
    use HasFactory;

    protected $table = 'acknowledgements';

    protected $fillable = [
        'policy_window_id',
        'employee_code',
        'status',
        'signer_name',
        'signature_payload',
        'signature_hash',
        'acknowledged_at',
    ];

    protected $casts = [
        'signature_payload' => 'array',
        'acknowledged_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function window(): BelongsTo {
        return $this->belongsTo(PolicyWindow::class, 'policy_window_id');
    }

    public function employee(): BelongsTo {
        return $this->belongsTo(User::class, 'employee_code', 'EmpCode');
    }
}

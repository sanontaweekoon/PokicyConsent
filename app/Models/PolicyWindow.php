<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PolicyWindow extends Model
{
    use HasFactory;

    protected $table = 'policy_windows';

    protected $fillable = [
        'policy__id',
        'window_no',
        'start_at',
        'end_at',
        'is_open',
        'allow_late_ack',
        'created_by'
    ];

    protected $casts = [
        'start_at'       => 'datetime:Y-m-d H:i:s',
        'end_at'         => 'datetime:Y-m-d H:i:s',
        'is_open'        => 'boolean',
        'allow_late_ack' => 'boolean',
        'window_no'      => 'integer',
    ];

    public function policy(): BelongsTo{
        return $this->belongsTo(Policy::class, 'policy_id');
    }

    public function creator(): BelongsTo{
        return $this->belongsTo(User::class, 'created_by');
    }
}

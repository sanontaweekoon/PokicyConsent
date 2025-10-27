<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class PolicyWindow extends Model
{
    use HasFactory;

    protected $table = 'policy_windows';

    protected $fillable = [
        'policy_id',
        'window_no',
        'start_at',
        'end_at',
        'is_open',
        'allow_late_ack',
        'create_by',
    ];

    protected $casts = [
        'start_at'       => 'datetime',
        'end_at'         => 'datetime',
        'is_open'        => 'boolean',
        'allow_late_ack' => 'boolean',
        'window_no'      => 'integer',
        'create_by'      => 'integer',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'policy_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_by');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(Acknowledgement::class, 'policy_window_id');
    }

    public function targetResolved(): HasMany
    {
        return $this->hasMany(PolicyTargetResolved::class, 'policy_window_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(PolicyAnnouncement::class, 'policy_window_id');
    }

    public function isOpen(): bool
    {   
        if ($this->is_open) {
            return true;
        }
        
        $now   = now();
        $start = $this->start_at;
        $end   = $this->end_at;

        if ($this->allow_late_ack) {
            return $start ? $now->greaterThanOrEqualTo($start) : true;
        }

        if ($start && $end) {
            return $now->between($start, $end, true);
        }

        if ($start && ! $end) {
            return $now->greaterThanOrEqualTo($start);
        }

        if (! $start && $end) {
            return $now->lessThanOrEqualTo($end);
        }
        return false;
    }

    // Generate a signed URL for this policy window valid for the given number of days
    public function signedLink(): string
    {
        return URL::signedRoute(
            'ack.window',
            ['window' => $this->id]
        );
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true)
            ->orWhere(function ($q) {
                $q->whereNotNull('start_at')->where('start_at', '<=', now());
            })
            ->orWhere(function ($q) {
                $q->whereNotNull('end_at')->where('end_at', '>=', now());
            });
    }
}

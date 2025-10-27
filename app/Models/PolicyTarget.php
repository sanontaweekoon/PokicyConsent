<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyTarget extends Model
{
    use HasFactory;

    protected $table   = 'policy_targets';
    public $timestamps = true;

    protected $fillable = [
        'policy_window_id',
        'target_type',
        'target_id',
        'include_descendants',
        'required',
        'index',
    ];

    protected $casts = [
        'policy_window_id'    => 'integer',
        'target_type'         => 'string',
        'target_id'           => 'integer',
        'include_descendants' => 'boolean',
        'required'            => 'boolean',
    ];

    public function window()
    {
        return $this->belongsTo(PolicyWindow::class, 'policy_window_id');
    }

    public function getPolicyAttribute()
    {
        return $this->window?->policy;
    }
}

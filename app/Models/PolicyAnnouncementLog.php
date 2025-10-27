<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyAnnouncementLog extends Model
{
    use HasFactory;

    protected $table   = 'policy_announcement_logs';
    public $timestamps = true;

    protected $fillable = [
        'announcement_id',
        'user_id',
        'status',
        'meta',
        'index',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function announcement(){
        return $this->belongsTo(PolicyAnnouncement::class, 'policy_announcement_id'); 
    }

    public function window(){
        return $this->belongsTo(PolicyWindow::class, 'policy_window_id');
    }

}

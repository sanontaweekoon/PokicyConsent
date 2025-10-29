<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyAnnouncement extends Model
{
    use HasFactory;

    protected $table = 'policy_announcements';
    public $timestamps = true;

    protected $fillable = [
        'policy_window_id',
        'channel_id',
        'subject',
        'content_html',
        'content_text',
        'sender_name',
        'send_at',
        'status'
    ];

    protected $casts = [
        'send_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function window(){
        return $this->belongsTo(PolicyWindow::class, 'policy_window_id');
    }

    public function logs(){
        return $this->hasMany(PolicyAnnouncementLog::class, 'policy_announcement_id');
    }

}
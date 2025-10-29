<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_SCHEDULED
    ];

    protected $table = 'policies';
    public $timestamps = true;
    protected $fillable = [
        'category_id',
        'code',
        'title',
        'description',
        'owner_user_id',
        'is_required_ack',
        'status',
        'recipient_type',
        'recipient_emails',
        'publish_at',
        'publish_date',
        'publish_time',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'is_required_ack' => 'boolean',
        'recipient_emails' => 'array',
        'publish_at' => 'datetime',
        'publish_date' => 'date',
    ];

    public function policyWindows()
    {
        return $this->hasMany(PolicyWindow::class, 'policy_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(PolicyCategory::class, 'category_id');
    }

    public function recipientGroups()
    {
        return $this->belongsToMany(
            RecipientGroup::class,
            'policy_recipient_groups',
            'policy_id',
            'recipient_group_id'
        )->withTimestamps();
    }

    public function getAllRecipientEmails()
    {
        $emails = [];
        foreach ($this->recipientGroups as $group) {
            $emails = array_merge($emails, $group->getEmails());
        }
        return array_unique($emails);
    }
}

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
        'owner_org_unit_id',
        'is_required_ack',
        'status',
        'publish_at',
        'publish_date',
        'publish_time',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'is_required_ack' => 'boolean',
        'publish_at' => 'datetime',
        'publish_date' => 'date',
    ];
}

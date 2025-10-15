<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'policies';
    public $timestamps = false;
    protected $fillable = [
        'category_id',
        'code',
        'title',
        'description',
        'owner_user_id',
        'owner_org_unit_id',
        'is_required_ack',
        'status',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'is_required_ack' => 'boolean',
        'publish_at' => 'datetime',
    ];
}

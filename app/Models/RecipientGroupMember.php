<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipientGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_group_id',
        'email',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function recipientGroup(): BelongsTo
    {
        return $this->belongsTo(RecipientGroup::class);
    }
}

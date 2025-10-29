<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipientGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'created_by',
    ];


    public function member(): HasMany
    {
        return $this->hasMany(RecipientGroupMember::class);
    }

    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    public function getEmails()
    {
        return $this->members()->pluck('email')->toArray();
    }

}

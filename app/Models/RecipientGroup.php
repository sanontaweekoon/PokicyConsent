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


    public function members(): HasMany
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

    public function getMembersData()
    {
        return $this->members()
            ->select(['email', 'name'])
            ->get()
            ->map(fn($m) => [
                'email' => $m->email,
                'name' => $m->name
            ])
            ->toArray();
    }
}

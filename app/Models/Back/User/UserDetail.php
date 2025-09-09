<?php

namespace App\Models\Back\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id','fname','lname','address','zip','city','state','phone',
        'avatar','bio','social','role','status',
    ];
    
    protected $casts = [
        'status' => 'bool',
    ];
    
    public const ROLES = ['master','admin','manager','editor','customer'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Niceties
    public function getFullNameAttribute(): string
    {
        return trim(($this->fname ?? '').' '.($this->lname ?? '')) ?: ($this->user?->name ?? '');
    }
    
    // Query helpers
    public function scopeForRole($q, string $role)
    {
        return $q->where('role', $role);
    }
}

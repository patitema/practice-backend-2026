<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role for the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        // Check if role relationship is loaded
        if ($this->relationLoaded('role')) {
            return $this->role && $this->role->name === $roleName;
        }
        
        // Fallback: check by role ID
        $roleId = $this->getAttribute('role');
        $roleMap = [
            'author' => 1,
            'respondent' => 2,
            'admin' => 3,
        ];
        
        return isset($roleMap[$roleName]) && $roleId === $roleMap[$roleName];
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is author.
     */
    public function isAuthor(): bool
    {
        return $this->hasRole('author');
    }

    /**
     * Get the surveys authored by the user.
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'author_id');
    }

    /**
     * Get the responses submitted by the user.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'respondent_id');
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'role',
        'password_hash',
        'first_name',
        'last_name',
        'birth_day',
        'birth_month',
        'birth_year',
        'gender',
        'city',
        'county',
        'phone',
        'avatar_url',
        'bio',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'email_verification_token',
        'email_verification_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'email_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'birth_day' => 'integer',
            'birth_month' => 'integer',
            'birth_year' => 'integer',
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getNameAttribute(): string
    {
        $first = $this->first_name ?? '';
        $last = $this->last_name ?? '';
        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : $this->email;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function projectsCreated()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function teamsCreated()
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot(['role', 'joined_at', 'left_at']);
    }
}

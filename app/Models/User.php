<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

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
        'member_code',
        'theme_preference',
        'locale_preference',
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

    public function isAdmin(): bool
    {
        $admins = array_map('strtolower', config('uniprojectmanager.admin_emails', []));
        if (empty($admins)) {
            return false;
        }

        return in_array(strtolower((string) $this->email), $admins, true);
    }

    public static function generateMemberCode(string $role): string
    {
        $prefix = $role === 'profesor' ? 'PROF' : 'STU';

        do {
            $code = $prefix . '-' . Str::upper(Str::random(6));
        } while (self::where('member_code', $code)->exists());

        return $code;
    }

    public function projectsCreated()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function classroomsOwned(): HasMany
    {
        return $this->hasMany(Classroom::class, 'created_by');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_members', 'user_id', 'classroom_id')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function classroomGrades(): HasMany
    {
        return $this->hasMany(ClassroomGrade::class, 'student_user_id');
    }

    public function gradesGiven(): HasMany
    {
        return $this->hasMany(ClassroomGrade::class, 'graded_by_user_id');
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
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

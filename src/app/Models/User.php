<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'deleted_at' => 'datetime',
        ];
    }

    public function teams(): BelongsToMany
    {
        // Припускаємо, що проміжна таблиця team_user має поле 'role' та 'deleted_at'
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps() // Якщо в team_user є timestamps
            ->wherePivotNull('deleted_at'); // Отримуємо лише активні членства
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function tasksReported(): HasMany
    {
        return $this->hasMany(Task::class, 'reporter_id');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot(['assigned_at', 'unassigned_at'])
            ->withTimestamps();
    }
}
